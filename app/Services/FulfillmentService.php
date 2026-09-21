<?php

namespace App\Services;

use App\Transaction;
use Illuminate\Support\Facades\Auth;

class FulfillmentService
{
    public const PIPELINE_SOURCES = ['ecommerce', 'sale_app'];

    public const STAGES = ['picking', 'packing', 'shipment', 'shipped'];

    public function pipelineQuery(int $business_id, $permitted_locations)
    {
        $q = Transaction::where('transactions.business_id', $business_id)
            ->whereIn('transactions.type', ['sales_order', 'sell'])
            ->where(function ($w) {
                $w->whereIn('transactions.order_source', self::PIPELINE_SOURCES)
                    ->orWhere(function ($s) {
                        $s->whereNull('transactions.order_source')->whereIn('transactions.source', self::PIPELINE_SOURCES);
                    });
            })
            ->where(function ($st) {
                $st->whereIn('transactions.status', ['ordered', 'partial', 'final', 'completed'])
                    ->orWhereNull('transactions.status');
            });

        if ($permitted_locations !== 'all') {
            $q->whereIn('transactions.location_id', $permitted_locations);
        }

        return $q;
    }

    public function forStage($query, string $stage)
    {
        if ($stage === 'picking') {
            return $query->where(function ($q) {
                $q->where('transactions.fulfillment_stage', 'picking')
                    ->orWhere(function ($o) {
                        $o->whereNull('transactions.fulfillment_stage')
                            ->where(function ($s) {
                                $s->whereNull('transactions.shipping_status')->orWhere('transactions.shipping_status', 'ordered');
                            });
                    });
            });
        }
        if ($stage === 'packing') {
            return $query->where('transactions.fulfillment_stage', 'packing');
        }
        if ($stage === 'shipment') {
            return $query->where('transactions.fulfillment_stage', 'shipment');
        }

        return $query->where('transactions.fulfillment_stage', 'shipped');
    }

    public function applySourceFilter($query, ?string $source)
    {
        if ($source === 'ecommerce' || $source === 'sale_app') {
            $query->where(function ($q) use ($source) {
                $q->where('transactions.order_source', $source)->orWhere(function ($s) use ($source) {
                    $s->whereNull('transactions.order_source')->where('transactions.source', $source);
                });
            });
        }

        return $query;
    }

    public function appendTimeline(Transaction $order, string $event, string $label, ?string $note = null): void
    {
        $meta = $order->fulfillment_meta ?? [];
        $timeline = $meta['timeline'] ?? [];
        $timeline[] = [
            'event' => $event,
            'label' => $label,
            'at' => now()->toDateTimeString(),
            'by' => optional(Auth::user())->first_name ?? 'Staff',
            'note' => $note,
        ];
        $meta['timeline'] = $timeline;
        $order->fulfillment_meta = $meta;
    }

    public function ensurePipelineFields(Transaction $order): void
    {
        $src = $order->order_source ?: $order->source;
        if (in_array($src, self::PIPELINE_SOURCES, true) && empty($order->order_source)) {
            $order->order_source = $src;
        }
        if (in_array($src, self::PIPELINE_SOURCES, true) && empty($order->fulfillment_stage)) {
            $ship = (string) ($order->shipping_status ?? '');
            $order->fulfillment_stage = $ship === 'packed' ? 'packing' : ($ship === 'shipped' || $ship === 'delivered' ? 'shipped' : 'picking');
            if ($order->fulfillment_stage === 'picking' && empty($order->shipping_status)) {
                $order->shipping_status = 'ordered';
            }
            $order->save();
        }
    }

    public function savePicks(Transaction $order, array $picks, bool $advance): void
    {
        foreach ($order->sell_lines as $line) {
            if (! empty($line->parent_sell_line_id)) {
                continue;
            }
            $qty = isset($picks[$line->id]) ? (float) $picks[$line->id] : (float) ($line->qty_picked ?? 0);
            $max = (float) $line->quantity;
            $line->qty_picked = max(0, min($qty, $max));
            $line->save();
        }
        $this->appendTimeline($order, 'picking_saved', 'Picking quantities updated');
        if ($advance) {
            $this->advanceToPacking($order);
        } else {
            $order->save();
        }
    }

    public function allPicked(Transaction $order): bool
    {
        foreach ($order->sell_lines as $line) {
            if (! empty($line->parent_sell_line_id)) {
                continue;
            }
            if ((float) ($line->qty_picked ?? 0) + 0.0001 < (float) $line->quantity) {
                return false;
            }
        }

        return $order->sell_lines->whereNull('parent_sell_line_id')->count() > 0;
    }

    public function advanceToPacking(Transaction $order): void
    {
        if (! $this->allPicked($order)) {
            throw new \RuntimeException(__('fulfillment.pick_all_items'));
        }
        foreach ($order->sell_lines as $line) {
            if (empty($line->parent_sell_line_id)) {
                $line->qty_packed = $line->qty_picked;
                $line->save();
            }
        }
        $order->fulfillment_stage = 'packing';
        $order->shipping_status = 'packed';
        $this->appendTimeline($order, 'moved_packing', 'Moved from Picking to Packing');
        $order->save();
    }

    public function savePacking(Transaction $order, array $checklist, array $package, bool $advance): void
    {
        $meta = $order->fulfillment_meta ?? [];
        $meta['pack_checklist'] = $checklist;
        $meta['package'] = $package;
        $order->fulfillment_meta = $meta;
        $this->appendTimeline($order, 'packing_saved', 'Packing details updated');
        if ($advance) {
            $this->advanceToShipment($order);
        } else {
            $order->save();
        }
    }

    public function packingComplete(array $checklist): bool
    {
        foreach (['items_verified', 'packaging_selected', 'packing_slip', 'package_sealed'] as $key) {
            if (empty($checklist[$key])) {
                return false;
            }
        }

        return true;
    }

    public function advanceToShipment(Transaction $order): void
    {
        $checklist = ($order->fulfillment_meta ?? [])['pack_checklist'] ?? [];
        if (! $this->packingComplete($checklist)) {
            throw new \RuntimeException(__('fulfillment.complete_packing_checklist'));
        }
        $order->fulfillment_stage = 'shipment';
        $order->shipping_status = 'packed';
        $this->appendTimeline($order, 'moved_shipment', 'Moved from Packing to Shipment');
        $order->save();
    }

    public function saveShipment(Transaction $order, array $ship, bool $confirm): void
    {
        $meta = $order->fulfillment_meta ?? [];
        $meta['shipment'] = $ship;
        $order->fulfillment_meta = $meta;
        if (! empty($ship['tracking'])) {
            $order->shipping_tracking_no = $ship['tracking'];
        }
        if (! empty($ship['carrier'])) {
            $order->shipping_method = $ship['carrier'];
        }
        $this->appendTimeline($order, 'shipment_saved', 'Shipment details updated');
        if ($confirm) {
            $this->markShipped($order);
        } else {
            $order->save();
        }
    }

    public function markShipped(Transaction $order): void
    {
        $order->fulfillment_stage = 'shipped';
        $order->shipping_status = 'shipped';
        $this->appendTimeline($order, 'shipped', 'Shipment confirmed / Marked shipped');
        $order->save();
    }

    public function pickedCount(Transaction $order): array
    {
        $lines = $order->sell_lines->whereNull('parent_sell_line_id');
        $total = $lines->count();
        $picked = $lines->filter(fn ($l) => (float) ($l->qty_picked ?? 0) >= (float) $l->quantity)->count();

        return ['picked' => $picked, 'total' => $total];
    }
}

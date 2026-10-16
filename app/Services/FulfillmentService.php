<?php

namespace App\Services;

use App\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FulfillmentService
{
    public const PIPELINE_SOURCES = ['ecommerce', 'sale_app', 'customer_app'];

    public const STAGES = ['picking', 'packing', 'shipment', 'shipped'];

    /** Map warehouse stage → ecommerce-style fulfillment status. */
    public const STAGE_TO_STATUS = [
        'picking' => 'processing',
        'packing' => 'packed',
        'shipment' => 'packed',
        'shipped' => 'shipped',
    ];

    /** Map ecommerce-style status → warehouse stage. */
    public const STATUS_TO_STAGE = [
        'pending' => 'picking',
        'confirmed' => 'picking',
        'processing' => 'picking',
        'packed' => 'shipment',
        'shipped' => 'shipped',
        'delivered' => 'shipped',
    ];

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
            // "Shipments" menu = ready to ship + already shipped (history).
            // Optional filter: ready | shipped (via applyShipmentFilter).
            return $query->whereIn('transactions.fulfillment_stage', ['shipment', 'shipped']);
        }

        return $query->where('transactions.fulfillment_stage', 'shipped');
    }

    /**
     * Narrow the Shipments queue: ready (awaiting confirm) or shipped (done).
     */
    public function applyShipmentFilter($query, ?string $shipment_filter)
    {
        if ($shipment_filter === 'ready') {
            $query->where('transactions.fulfillment_stage', 'shipment');
        } elseif ($shipment_filter === 'shipped') {
            $query->where('transactions.fulfillment_stage', 'shipped');
        }

        return $query;
    }

    /**
     * Keep warehouse stage in sync when shipping status is changed from Sales Order page.
     */
    public function syncStageFromShippingStatus(Transaction $order): void
    {
        $src = $order->order_source ?: $order->source;
        if (! in_array($src, self::PIPELINE_SOURCES, true)) {
            return;
        }
        if (empty($order->order_source)) {
            $order->order_source = $src;
        }

        $ship = (string) ($order->shipping_status ?? '');
        if ($ship === 'packed') {
            if (! in_array($order->fulfillment_stage, ['packing', 'shipment', 'shipped'], true)) {
                $order->fulfillment_stage = 'shipment';
            }

            return;
        }
        if ($ship === 'shipped' || $ship === 'delivered') {
            $order->loadMissing('sell_lines');
            $hasWave = false;
            foreach ($this->sellLines($order) as $line) {
                if ($this->qtyPicked($line) > $this->qtyShipped($line)) {
                    $line->qty_packed = $this->qtyPicked($line);
                    $line->save();
                    $hasWave = true;
                }
            }
            if ($hasWave) {
                $this->markShipped($order);

                return;
            }
            $order->fulfillment_stage = $this->hasRemainingToFulfill($order) ? 'picking' : 'shipped';

            return;
        }
        if ($ship === 'ordered' || $ship === '') {
            if ($order->fulfillment_stage !== 'shipped' || $this->hasRemainingToFulfill($order)) {
                $order->fulfillment_stage = 'picking';
            }
        }
    }

    public function applySourceFilter($query, ?string $source)
    {
        if (in_array($source, self::PIPELINE_SOURCES, true)) {
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

        $this->reconcileStage($order);
    }

    /**
     * Fix stuck stages when qty_shipped lags picks (e.g. marked shipped before qty_shipped existed),
     * and reopen picking when a partial shipment still has due qty.
     */
    public function reconcileStage(Transaction $order): void
    {
        $src = $order->order_source ?: $order->source;
        if (! in_array($src, self::PIPELINE_SOURCES, true)) {
            return;
        }

        $order->loadMissing('sell_lines');
        if (! $this->hasRemainingToFulfill($order)) {
            return;
        }

        // Marked complete but line qty_shipped still behind packed/picked.
        if ($order->fulfillment_stage === 'shipped') {
            if ($this->canAdvancePickWave($order)) {
                if (in_array((string) $order->shipping_status, ['shipped', 'delivered'], true)) {
                    // Backfill the wave that was never written to qty_shipped.
                    $this->markShipped($order);
                } else {
                    $order->fulfillment_stage = 'shipment';
                    $order->shipping_status = 'packed';
                    $this->appendTimeline($order, 'reopened_shipment', 'Reopened shipment — unshipped picked qty');
                    $order->save();
                }

                return;
            }

            $order->fulfillment_stage = 'picking';
            $order->shipping_status = 'ordered';
            $this->appendTimeline($order, 'reopened_picking', 'Reopened picking — remaining qty after partial ship');
            $order->save();
            $this->syncMetaStatusFromStage($order, 'processing', 'Remaining items — back to picking');
        }
    }

    /**
     * Same fulfillment meta record ecommerce uses — so Sales App / Customer App
     * get the same status dropdown, tracking token and timeline on the SO page.
     */
    public function ensureOrderMeta(Transaction $order)
    {
        if (! class_exists(\Modules\Ecommerce\Entities\EcomOrderMeta::class)) {
            return null;
        }

        $src = $order->order_source ?: $order->source;
        if (! in_array($src, self::PIPELINE_SOURCES, true)) {
            return null;
        }

        $this->ensurePipelineFields($order);
        $order->refresh();

        $initial = self::STAGE_TO_STATUS[$order->fulfillment_stage ?? ''] ?? 'pending';
        $sourceLabel = match ($src) {
            'sale_app' => 'Sales App',
            'customer_app' => 'Customer App',
            'ecommerce' => 'Online Store',
            default => (string) $src,
        };

        return \Modules\Ecommerce\Entities\EcomOrderMeta::firstOrCreate(
            ['transaction_id' => $order->id],
            [
                'business_id' => $order->business_id,
                'tracking_token' => Str::random(40),
                'fulfillment_status' => $initial,
                'timeline' => [[
                    'status' => $initial,
                    'label' => 'Order received ('.$sourceLabel.')',
                    'at' => now()->toDateTimeString(),
                    'note' => null,
                ]],
            ]
        );
    }

    /**
     * Apply ecommerce-style fulfillment status onto shipping + warehouse stage.
     */
    public function applyFulfillmentStatus(Transaction $order, string $status): void
    {
        if (empty($order->order_source)) {
            $src = $order->source;
            if (in_array($src, self::PIPELINE_SOURCES, true)) {
                $order->order_source = $src;
            }
        }

        // "Shipped" from the SO page uses the same partial-wave logic.
        if ($status === 'shipped' || $status === 'delivered') {
            $order->loadMissing('sell_lines');
            foreach ($this->sellLines($order) as $line) {
                $picked = $this->qtyPicked($line);
                $shipped = $this->qtyShipped($line);
                if ($picked > $shipped) {
                    $line->qty_packed = $picked;
                    $line->save();
                }
            }
            $this->markShipped($order);
            if ($status === 'delivered' && ! $this->hasRemainingToFulfill($order)) {
                $order->shipping_status = 'delivered';
                $order->save();
            }

            return;
        }

        $shipping_map = [
            'pending' => 'ordered',
            'confirmed' => 'ordered',
            'processing' => 'ordered',
            'packed' => 'packed',
            'cancelled' => 'cancelled',
        ];
        if (isset($shipping_map[$status])) {
            $order->shipping_status = $shipping_map[$status];
        }
        if (isset(self::STATUS_TO_STAGE[$status])) {
            $order->fulfillment_stage = self::STATUS_TO_STAGE[$status];
        }
    }

    public function sellLines($order)
    {
        return $order->sell_lines->whereNull('parent_sell_line_id');
    }

    public function qtyShipped($line): float
    {
        return (float) ($line->qty_shipped ?? 0);
    }

    public function qtyPicked($line): float
    {
        return (float) ($line->qty_picked ?? 0);
    }

    public function qtyDue($line): float
    {
        return max(0, (float) $line->quantity - $this->qtyPicked($line));
    }

    /** Qty picked this wave (not yet shipped). */
    public function qtyWave($line): float
    {
        return max(0, $this->qtyPicked($line) - $this->qtyShipped($line));
    }

    public function hasRemainingToFulfill(Transaction $order): bool
    {
        foreach ($this->sellLines($order) as $line) {
            if ((float) $line->quantity > $this->qtyShipped($line) + 0.0001) {
                return true;
            }
        }

        return false;
    }

    public function savePicks(Transaction $order, array $pickNow, bool $advance): void
    {
        foreach ($this->sellLines($order) as $line) {
            $max = (float) $line->quantity;
            $already = $this->qtyPicked($line);
            $now = isset($pickNow[$line->id]) ? (float) $pickNow[$line->id] : 0;
            if ($now < 0) {
                $now = 0;
            }
            // Add this session's pick onto already saved — never replace with ordered qty.
            $line->qty_picked = max(0, min($already + $now, $max));
            $line->save();
        }
        $this->appendTimeline($order, 'picking_saved', 'Picking quantities updated');
        if ($advance) {
            $this->advanceToPacking($order);
        } else {
            $order->save();
        }
    }

    /**
     * Ready for packing when at least one line has newly picked qty not yet shipped.
     */
    public function canAdvancePickWave(Transaction $order): bool
    {
        foreach ($this->sellLines($order) as $line) {
            if ($this->qtyWave($line) > 0.0001) {
                return true;
            }
        }

        return false;
    }

    /** @deprecated use canAdvancePickWave */
    public function allPicked(Transaction $order): bool
    {
        return $this->canAdvancePickWave($order);
    }

    public function advanceToPacking(Transaction $order): void
    {
        if (! $this->canAdvancePickWave($order)) {
            throw new \RuntimeException(__('fulfillment.pick_wave_items'));
        }
        foreach ($this->sellLines($order) as $line) {
            // Pack cumulative picked so far (includes prior shipped + this wave).
            $line->qty_packed = $this->qtyPicked($line);
            $line->save();
        }
        $order->fulfillment_stage = 'packing';
        $order->shipping_status = 'packed';
        $this->appendTimeline($order, 'moved_packing', 'Moved from Picking to Packing (this wave)');
        $order->save();
        $this->syncMetaStatusFromStage($order, 'packed', 'Moved to Packing');
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
        $this->syncMetaStatusFromStage($order, 'packed', 'Moved to Shipment');
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
        $order->loadMissing('sell_lines');
        $waveLines = [];
        $waveQty = 0.0;

        foreach ($this->sellLines($order) as $line) {
            $ordered = (float) $line->quantity;
            $packed = (float) ($line->qty_packed ?? $line->qty_picked ?? 0);
            $prevShipped = $this->qtyShipped($line);
            $shipThisWave = max(0, min($packed, $ordered) - $prevShipped);
            $line->qty_shipped = min($ordered, $prevShipped + $shipThisWave);
            $line->save();
            if ($shipThisWave > 0.0001) {
                $waveLines[] = [
                    'sell_line_id' => $line->id,
                    'product' => $line->product->name ?? ('#'.$line->product_id),
                    'qty' => $shipThisWave,
                ];
                $waveQty += $shipThisWave;
            }
        }

        $this->appendTimeline(
            $order,
            'shipped_wave',
            $waveQty > 0
                ? 'Shipment confirmed — '.$waveQty.' unit(s) in this wave'
                : 'Shipment confirmed'
        );

        $meta = $order->fulfillment_meta ?? [];
        $waves = $meta['waves'] ?? [];
        $waves[] = [
            'at' => now()->toDateTimeString(),
            'carrier' => $meta['shipment']['carrier'] ?? $order->shipping_method,
            'tracking' => $meta['shipment']['tracking'] ?? $order->shipping_tracking_no,
            'qty' => $waveQty,
            'lines' => $waveLines,
        ];
        $meta['waves'] = $waves;
        // Clear packing/shipment form for the next wave.
        unset($meta['pack_checklist'], $meta['package'], $meta['shipment']);
        $order->fulfillment_meta = $meta;

        if ($this->hasRemainingToFulfill($order)) {
            // Remaining qty goes back to Picking for the next wave.
            $order->fulfillment_stage = 'picking';
            $order->shipping_status = 'ordered';
            $this->appendTimeline($order, 'partial_remaining', 'Partial shipment — remaining items returned to Picking');
            $order->save();
            $this->syncMetaStatusFromStage($order, 'processing', 'Partial shipment — pick remaining items');
        } else {
            $order->fulfillment_stage = 'shipped';
            $order->shipping_status = 'shipped';
            $this->appendTimeline($order, 'shipped', 'All items shipped — order complete');
            $order->save();
            $this->syncMetaStatusFromStage($order, 'shipped', 'Marked shipped (complete)');
        }
    }

    protected function syncMetaStatusFromStage(Transaction $order, string $status, string $label): void
    {
        try {
            $meta = $this->ensureOrderMeta($order);
            if (! $meta) {
                return;
            }
            $timeline = $meta->timeline ?? [];
            $timeline[] = [
                'status' => $status,
                'label' => $label,
                'at' => now()->toDateTimeString(),
                'note' => null,
            ];
            $meta->fulfillment_status = $status;
            $meta->timeline = $timeline;
            $meta->save();
        } catch (\Throwable $e) {
            \Log::warning('Fulfillment meta sync skipped: '.$e->getMessage());
        }
    }

    public function pickedCount(Transaction $order): array
    {
        $lines = $this->sellLines($order);
        $total = $lines->count();
        $open = $lines->filter(fn ($l) => $this->qtyDue($l) > 0.0001 || $this->qtyWave($l) > 0.0001)->count();
        $waveReady = $lines->filter(fn ($l) => $this->qtyWave($l) > 0.0001)->count();

        return ['picked' => $waveReady, 'total' => max($open, $total), 'wave' => $waveReady, 'open' => $open];
    }
}

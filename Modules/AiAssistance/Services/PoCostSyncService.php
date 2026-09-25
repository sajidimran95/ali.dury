<?php

namespace Modules\AiAssistance\Services;

use App\PurchaseLine;
use App\Transaction;
use App\Variation;
use App\Utils\ProductUtil;

class PoCostSyncService
{
    /** Set while this service saves a variation so the price observer does not double-alert. */
    public static bool $syncing = false;

    public function syncFromPurchaseLine(PurchaseLine $line): void
    {
        if (! $line->variation_id || ! $line->transaction_id) {
            return;
        }

        $txn = $line->relationLoaded('transaction')
            ? $line->transaction
            : Transaction::find($line->transaction_id);

        if (! $txn || $txn->type !== 'purchase_order') {
            return;
        }

        if (! $line->wasRecentlyCreated && ! $line->wasChanged('purchase_price') && ! $line->wasChanged('pp_without_discount')) {
            return;
        }

        $variation = Variation::with(['product', 'product.product_tax'])->find($line->variation_id);
        if (! $variation) {
            return;
        }

        $newCost = (float) ($line->pp_without_discount ?: $line->purchase_price);
        $oldCost = (float) $variation->default_purchase_price;

        if (round($oldCost, 4) === round($newCost, 4)) {
            return;
        }

        $tax_rate = (float) ($variation->product->product_tax->amount ?? 0);
        $util = app(ProductUtil::class);

        $variation->default_purchase_price = $newCost;
        $variation->dpp_inc_tax = $util->calc_percentage($newCost, $tax_rate, $newCost);
        $variation->profit_percent = $util->get_percent($newCost, (float) $variation->default_sell_price);
        self::$syncing = true;
        try {
            $variation->save();
        } finally {
            self::$syncing = false;
        }

        $row = [
            'product' => $variation->product->name ?? ('#'.$line->product_id),
            'sku' => $variation->sub_sku,
            'old_cost' => $oldCost,
            'new_cost' => $newCost,
            'po_ref' => $txn->ref_no,
        ];

        try {
            app(AdminAlertService::class)->queue(
                (int) $txn->business_id,
                AdminAlertService::TYPE_PO_COST,
                'po_cost:'.$txn->id,
                $row,
                [
                    'po_id' => $txn->id,
                    'po_ref' => $txn->ref_no,
                    'url' => action([\App\Http\Controllers\PurchaseOrderController::class, 'edit'], [$txn->id]),
                ]
            );
        } catch (\Throwable $e) {
            \Log::warning('JapsAI PO cost alert skipped: '.$e->getMessage());
        }

        if (! app()->bound('session')) {
            return;
        }

        $list = session('japs_po_cost_changes', []);
        $list[] = $row;
        // Keep for this request (AJAX chat) and flash for next full page (modal alert).
        session()->put('japs_po_cost_changes', $list);
        session()->flash('japs_po_cost_changes', $list);
    }
}

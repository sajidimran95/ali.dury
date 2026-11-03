<?php

namespace App\Utils;

use App\Business;
use App\Contact;
use App\DeletedSale;
use App\Events\ProductsCreatedOrModified;
use App\Events\TransactionPaymentAdded;
use App\Product;
use App\Transaction;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\Variation;
use App\VariationLocationDetails;
use Illuminate\Support\Facades\DB;

/**
 * Soft delete / restore / permanent delete for products and sales.
 *
 * Products: soft deleted in place (products.deleted_at). Their variations are soft deleted with the
 * same timestamp so they vanish from POS / search, while invoices keep showing the product name.
 *
 * Sales: the existing deleteSale() flow still reverses stock, payments, accounts and reward points,
 * but a full snapshot is stored in `deleted_sales` first, so the sale can be rebuilt on restore.
 */
class RecycleBinUtil
{
    /* ------------------------------------------------------------------ */
    /*  Products                                                           */
    /* ------------------------------------------------------------------ */

    public function softDeleteProduct(Product $product, $user_id = null)
    {
        $now = now();

        Variation::where('product_id', $product->id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => $now]);

        // Set timestamp explicitly so variations can be matched on restore.
        $product->deleted_by = $user_id;
        $product->deleted_at = $now;
        $product->saveQuietly();

        event(new ProductsCreatedOrModified($product, 'deleted'));
    }

    /**
     * @return array ['success' => bool, 'msg' => string]
     */
    public function restoreProduct($business_id, $product_id)
    {
        $product = Product::onlyTrashed()
            ->where('business_id', $business_id)
            ->find($product_id);

        if (empty($product)) {
            return ['success' => false, 'msg' => __('lang_v1.recycle_item_not_found')];
        }

        $sku_taken = Product::where('business_id', $business_id)
            ->where('sku', $product->sku)
            ->exists();
        if ($sku_taken) {
            return ['success' => false, 'msg' => __('lang_v1.recycle_product_sku_conflict', ['sku' => $product->sku])];
        }

        DB::transaction(function () use ($product) {
            Variation::onlyTrashed()
                ->where('product_id', $product->id)
                ->where('deleted_at', $product->deleted_at)
                ->update(['deleted_at' => null]);

            $product->deleted_by = null;
            $product->restore();

            event(new ProductsCreatedOrModified($product, 'updated'));
        });

        return ['success' => true, 'msg' => __('lang_v1.recycle_product_restored', ['name' => $product->name])];
    }

    /**
     * Number of documents (sales, purchases, adjustments, ...) referencing the product.
     * Such products are kept forever so the old invoices keep their lines.
     */
    public function productUsageCount($product_id)
    {
        return DB::table('transaction_sell_lines')->where('product_id', $product_id)->count()
            + DB::table('purchase_lines')->where('product_id', $product_id)->count()
            + DB::table('stock_adjustment_lines')->where('product_id', $product_id)->count();
    }

    /**
     * @return bool true if purged, false if kept because it is used in documents
     */
    public function forceDeleteProduct(Product $product)
    {
        if ($this->productUsageCount($product->id) > 0) {
            return false;
        }

        if (class_exists(\Modules\Manufacturing\Entities\MfgRecipeIngredient::class)
            && DB::getSchemaBuilder()->hasTable('mfg_recipe_ingredients')) {
            $variation_ids = Variation::withTrashed()->where('product_id', $product->id)->pluck('id');
            if (\Modules\Manufacturing\Entities\MfgRecipeIngredient::whereIn('variation_id', $variation_ids)->exists()) {
                return false;
            }
        }

        VariationLocationDetails::where('product_id', $product->id)->delete();
        $product->product_locations()->detach();
        \App\ProductRack::where('product_id', $product->id)->delete();
        $product->forceDelete();

        return true;
    }

    /* ------------------------------------------------------------------ */
    /*  Sales                                                              */
    /* ------------------------------------------------------------------ */

    /**
     * Keep the original id when it is free, otherwise take MAX(id)+1.
     * Explicit ids also work on tables that lost their AUTO_INCREMENT.
     */
    private function freeId($table, $preferred_id)
    {
        if (! empty($preferred_id) && ! DB::table($table)->where('id', $preferred_id)->exists()) {
            return $preferred_id;
        }

        return ((int) DB::table($table)->max('id')) + 1;
    }

    /**
     * Store a full snapshot of the sale before deleteSale() removes it.
     */
    public function archiveSale(Transaction $transaction)
    {
        $sell_lines = DB::table('transaction_sell_lines')
            ->where('transaction_id', $transaction->id)
            ->orderBy('id')
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();

        $sell_line_ids = array_column($sell_lines, 'id');

        $warranties = empty($sell_line_ids) ? [] : DB::table('sell_line_warranties')
            ->whereIn('sell_line_id', $sell_line_ids)
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();

        $payments = DB::table('transaction_payments')
            ->where('transaction_id', $transaction->id)
            ->orderBy('id')
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();

        $row = (array) DB::table('transactions')->where('id', $transaction->id)->first();

        return DeletedSale::create([
            'business_id' => $transaction->business_id,
            'transaction_id' => $transaction->id,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'invoice_no' => $transaction->invoice_no,
            'contact_id' => $transaction->contact_id,
            'location_id' => $transaction->location_id,
            'final_total' => $transaction->final_total ?? 0,
            'transaction_date' => $transaction->transaction_date,
            'deleted_by' => auth()->check() ? auth()->id() : null,
            'payload' => [
                'transaction' => $row,
                'sell_lines' => $sell_lines,
                'payments' => $payments,
                'warranties' => $warranties,
            ],
        ]);
    }

    /**
     * Rebuild an archived sale: transaction, lines, payments, stock, purchase mapping,
     * account entries, reward points and sales order quantities.
     *
     * Must be called inside a DB transaction.
     *
     * @return array ['success' => bool, 'msg' => string]
     */
    public function restoreSale($business_id, $deleted_sale_id)
    {
        $archive = DeletedSale::where('business_id', $business_id)->find($deleted_sale_id);
        if (empty($archive)) {
            return ['success' => false, 'msg' => __('lang_v1.recycle_item_not_found')];
        }

        $payload = $archive->payload;
        $t_row = $payload['transaction'] ?? [];
        $lines = $payload['sell_lines'] ?? [];
        $payments = $payload['payments'] ?? [];
        $warranties = $payload['warranties'] ?? [];

        if (empty($t_row)) {
            return ['success' => false, 'msg' => __('lang_v1.recycle_item_not_found')];
        }

        // ---- validations ------------------------------------------------
        if (! empty($t_row['contact_id']) && ! Contact::withTrashed()->where('id', $t_row['contact_id'])->exists()) {
            return ['success' => false, 'msg' => __('lang_v1.recycle_sale_contact_missing')];
        }

        $product_ids = array_values(array_unique(array_column($lines, 'product_id')));
        $products = Product::withTrashed()->whereIn('id', $product_ids)->get()->keyBy('id');

        $missing = array_diff($product_ids, $products->keys()->all());
        if (! empty($missing)) {
            return ['success' => false, 'msg' => __('lang_v1.recycle_sale_product_purged')];
        }

        $trashed = $products->filter(fn ($p) => $p->trashed())->pluck('name')->all();
        if (! empty($trashed)) {
            return ['success' => false, 'msg' => __('lang_v1.recycle_sale_restore_products_first', ['products' => implode(', ', $trashed)])];
        }

        $variation_ids = array_values(array_unique(array_column($lines, 'variation_id')));
        $existing_variations = Variation::withTrashed()->whereIn('id', $variation_ids)->pluck('id')->all();
        if (count(array_diff($variation_ids, $existing_variations)) > 0) {
            return ['success' => false, 'msg' => __('lang_v1.recycle_sale_product_purged')];
        }

        if (! empty($t_row['invoice_no'])) {
            $invoice_taken = Transaction::where('business_id', $business_id)
                ->where('type', $t_row['type'])
                ->where('invoice_no', $t_row['invoice_no'])
                ->exists();
            if ($invoice_taken) {
                return ['success' => false, 'msg' => __('lang_v1.recycle_sale_invoice_conflict', ['invoice' => $t_row['invoice_no']])];
            }
        }

        // ---- rebuild rows -------------------------------------------------
        $original_id = $t_row['id'];
        $t_row['id'] = $this->freeId('transactions', $original_id);
        DB::table('transactions')->insert($t_row);
        $transaction_id = $t_row['id'];

        // Clean up rows orphaned by installs without FK cascades, so nothing gets duplicated.
        if ($transaction_id == $original_id) {
            DB::table('transaction_payments')->where('transaction_id', $original_id)->delete();
        }

        // Parents before children (modifiers / combo items reference parent_sell_line_id).
        usort($lines, fn ($a, $b) => (empty($a['parent_sell_line_id']) ? 0 : 1) <=> (empty($b['parent_sell_line_id']) ? 0 : 1));
        $line_map = [];
        foreach ($lines as $line) {
            $old_id = $line['id'];
            $line['transaction_id'] = $transaction_id;
            if (! empty($line['parent_sell_line_id'])) {
                $line['parent_sell_line_id'] = $line_map[$line['parent_sell_line_id']] ?? null;
            }
            if (! empty($line['so_line_id']) && ! TransactionSellLine::where('id', $line['so_line_id'])->exists()) {
                $line['so_line_id'] = null;
            }
            $line['id'] = $this->freeId('transaction_sell_lines', $old_id);
            DB::table('transaction_sell_lines')->insert($line);
            $line_map[$old_id] = $line['id'];
        }

        foreach ($warranties as $w) {
            if (isset($line_map[$w['sell_line_id']])) {
                $w['sell_line_id'] = $line_map[$w['sell_line_id']];
                unset($w['id']);
                DB::table('sell_line_warranties')->insert($w);
            }
        }

        $payment_ids = [];
        foreach ($payments as $p) {
            $p['transaction_id'] = $transaction_id;
            $p['id'] = $this->freeId('transaction_payments', $p['id']);
            DB::table('transaction_payments')->insert($p);
            $payment_ids[] = $p['id'];
        }

        $transaction = Transaction::with('sell_lines')->find($transaction_id);
        $transactionUtil = new TransactionUtil();
        $productUtil = new ProductUtil();

        // ---- side effects (inverse of TransactionUtil::deleteSale) --------
        // Sales order "invoiced" quantities are decremented on delete in both branches.
        foreach ($transaction->sell_lines as $sl) {
            if (! empty($sl->so_line_id)) {
                TransactionSellLine::where('id', $sl->so_line_id)->increment('so_quantity_invoiced', $sl->quantity);
            }
        }

        if ($transaction->status != 'draft') {
            // Stock was put back for every line on delete -> take it out again.
            foreach ($transaction->sell_lines as $sl) {
                if ($sl->quantity != 0) {
                    $productUtil->decreaseProductQuantity($sl->product_id, $sl->variation_id, $transaction->location_id, $sl->quantity);
                }
            }

            if ($transaction->type == 'sell' && $transaction->status == 'final') {
                $business = Business::find($business_id);
                $transactionUtil->mapPurchaseSell([
                    'id' => $business_id,
                    'accounting_method' => $business->accounting_method,
                    'location_id' => $transaction->location_id,
                    'pos_settings' => $business->pos_settings,
                ], $transaction->sell_lines, 'purchase', false);
            }

            foreach (TransactionPayment::whereIn('id', $payment_ids)->get() as $payment) {
                event(new TransactionPaymentAdded($payment, [
                    'account_id' => $payment->account_id,
                    'amount' => $payment->amount,
                    'transaction_type' => $transaction->type,
                    'is_return' => $payment->is_return,
                ]));
            }

            if (! empty($transaction->contact_id) && (! empty($transaction->rp_earned) || ! empty($transaction->rp_redeemed))) {
                $transactionUtil->updateCustomerRewardPoints($transaction->contact_id, $transaction->rp_earned, 0, $transaction->rp_redeemed, 0);
            }

            $transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
        }

        if (! empty($transaction->sales_order_ids)) {
            $transactionUtil->updateSalesOrderStatus($transaction->sales_order_ids);
        }

        $transactionUtil->activityLog($transaction, 'sell_restored', null, [
            'id' => $transaction->id,
            'invoice_no' => $transaction->invoice_no,
        ]);

        $archive->delete();

        return ['success' => true, 'msg' => __('lang_v1.recycle_sale_restored', ['invoice' => $transaction->invoice_no])];
    }
}

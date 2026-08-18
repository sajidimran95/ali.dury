<?php

namespace App\Services\Demo;

use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\ExpenseCategory;
use App\Product;
use App\PurchaseLine;
use App\StockAdjustmentLine;
use App\Transaction;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\Unit;
use App\User;
use App\Utils\ProductUtil;
use App\Variation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Inserts labelled DEMO records into an existing business so lists are not empty.
 * Does not wipe live data. Re-run with --force to replace previous DEMO rows only.
 */
class DemoOperationalDataSeeder
{
    public const MARKER = '[DEMO]';

    public function __construct(protected ProductUtil $productUtil)
    {
    }

    public function seed(int $businessId, bool $force = false, ?callable $log = null): array
    {
        $business = Business::find($businessId);
        if (empty($business)) {
            throw new \RuntimeException("Business #{$businessId} was not found.");
        }

        $location = BusinessLocation::where('business_id', $businessId)->orderBy('id')->first();
        if (empty($location)) {
            throw new \RuntimeException("No business location found for business #{$businessId}.");
        }

        $user = User::find($business->owner_id)
            ?: User::where('business_id', $businessId)->orderBy('id')->first();
        if (empty($user)) {
            throw new \RuntimeException("No user found for business #{$businessId}.");
        }

        $this->enableSalesOrder($business);
        $this->log($log, 'Sales order module enabled.');

        if ($force) {
            $this->removePreviousDemo($businessId);
            $this->log($log, 'Removed previous DEMO rows.');
        } elseif ($this->alreadySeeded($businessId)) {
            $this->log($log, 'DEMO data already present. Use --force to rebuild it.');

            return $this->counts($businessId);
        }

        DB::transaction(function () use ($businessId, $location, $user, $log) {
            $suppliers = $this->ensureSuppliers($businessId, $user->id);
            $customers = $this->ensureCustomers($businessId, $user->id);
            $this->log($log, 'Customers and suppliers ready.');

            $variations = $this->ensureProducts($businessId, $user->id);
            if ($variations->isEmpty()) {
                throw new \RuntimeException('Could not create or find products to seed.');
            }
            $this->log($log, $variations->count().' products ready.');

            $this->seedPurchases($businessId, $location->id, $user->id, $suppliers, $variations);
            $this->log($log, 'Purchases + purchase orders inserted.');

            $salesOrders = $this->seedSalesOrders($businessId, $location->id, $user->id, $customers, $variations);
            $this->log($log, 'Sales orders inserted.');

            $this->seedSales($businessId, $location->id, $user->id, $customers, $variations, $salesOrders);
            $this->log($log, 'Sales, drafts, and quotations inserted.');

            $this->seedExpenses($businessId, $location->id, $user->id);
            $this->seedStockAdjustment($businessId, $location->id, $user->id, $variations);
            $this->seedReturns($businessId, $location->id, $user->id, $customers, $suppliers, $variations);
            $this->log($log, 'Expenses, stock adjustment, and returns inserted.');
        });

        return $this->counts($businessId);
    }

    protected function alreadySeeded(int $businessId): bool
    {
        return Transaction::where('business_id', $businessId)
            ->where('additional_notes', 'like', self::MARKER.'%')
            ->exists();
    }

    protected function removePreviousDemo(int $businessId): void
    {
        $ids = Transaction::where('business_id', $businessId)
            ->where('additional_notes', 'like', self::MARKER.'%')
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        TransactionSellLine::whereIn('transaction_id', $ids)->delete();
        PurchaseLine::whereIn('transaction_id', $ids)->delete();
        TransactionPayment::whereIn('transaction_id', $ids)->delete();
        StockAdjustmentLine::whereIn('transaction_id', $ids)->delete();
        if (class_exists(\Modules\Ecommerce\Entities\EcomOrderMeta::class)) {
            \Modules\Ecommerce\Entities\EcomOrderMeta::whereIn('transaction_id', $ids)->delete();
        }
        Transaction::whereIn('id', $ids)->delete();
    }

    protected function enableSalesOrder(Business $business): void
    {
        $pos = $business->pos_settings;
        if (is_string($pos)) {
            $pos = json_decode($pos, true) ?: [];
        }
        if (! is_array($pos)) {
            $pos = [];
        }
        $pos['enable_sales_order'] = 1;
        $business->pos_settings = json_encode($pos);

        $common = $business->common_settings ?? [];
        if (is_string($common)) {
            $common = json_decode($common, true) ?: [];
        }
        if (! is_array($common)) {
            $common = [];
        }
        $common['enable_purchase_order'] = 1;
        $common['enable_purchase_requisition'] = 1;
        $business->common_settings = $common;
        $business->save();
    }

    protected function ensureSuppliers(int $businessId, int $userId)
    {
        $names = [
            ['Alamo Candy Co', '2145550101'],
            ['Gulf Coast Snacks', '7135550188'],
            ['Tennessee Tobacco Dist.', '6155550144'],
        ];
        $ids = [];
        foreach ($names as $i => [$name, $mobile]) {
            $contact = Contact::firstOrCreate(
                [
                    'business_id' => $businessId,
                    'mobile' => $mobile,
                    'type' => 'supplier',
                ],
                [
                    'name' => $name,
                    'supplier_business_name' => $name,
                    'created_by' => $userId,
                    'contact_id' => 'DEMO-SUP-'.($i + 1),
                    'email' => 'supplier'.($i + 1).'@demo.local',
                    'city' => 'Nashville',
                    'state' => 'TN',
                    'country' => 'USA',
                ]
            );
            $ids[] = $contact->id;
        }

        return Contact::whereIn('id', $ids)->get();
    }

    protected function ensureCustomers(int $businessId, int $userId)
    {
        $names = [
            ['Metro Mart', 'Walk-in / retail', '6155551001'],
            ['River City Wholesale', 'Wholesale', '6155551002'],
            ['Oak Street Convenience', 'Retail chain', '6155551003'],
            ['Highland Smoke Shop', 'Specialty', '6155551004'],
        ];
        $ids = [];
        foreach ($names as $i => [$name, $biz, $mobile]) {
            $contact = Contact::firstOrCreate(
                [
                    'business_id' => $businessId,
                    'mobile' => $mobile,
                    'type' => 'customer',
                ],
                [
                    'name' => $name,
                    'supplier_business_name' => $biz,
                    'created_by' => $userId,
                    'contact_id' => 'DEMO-CUS-'.($i + 1),
                    'email' => 'customer'.($i + 1).'@demo.local',
                    'city' => 'Nashville',
                    'state' => 'TN',
                    'country' => 'USA',
                ]
            );
            $ids[] = $contact->id;
        }

        $walkIn = Contact::where('business_id', $businessId)->where('is_default', 1)->first();
        if ($walkIn) {
            $ids[] = $walkIn->id;
        }

        return Contact::whereIn('id', array_unique($ids))->get();
    }

    protected function ensureProducts(int $businessId, int $userId)
    {
        $existing = Variation::query()
            ->whereHas('product', function ($q) use ($businessId) {
                $q->where('business_id', $businessId)->where('type', 'single');
            })
            ->with('product')
            ->limit(12)
            ->get();

        if ($existing->count() >= 4) {
            return $existing;
        }

        $unit = Unit::where('business_id', $businessId)->orderBy('id')->first();
        if (empty($unit)) {
            $unit = Unit::create([
                'business_id' => $businessId,
                'actual_name' => 'Pieces',
                'short_name' => 'Pc(s)',
                'allow_decimal' => 0,
                'created_by' => $userId,
            ]);
        }

        $category = Category::firstOrCreate(
            ['business_id' => $businessId, 'name' => 'Demo Grocery', 'category_type' => 'product', 'parent_id' => 0],
            ['created_by' => $userId, 'short_code' => 'DEMO']
        );
        $brand = Brands::firstOrCreate(
            ['business_id' => $businessId, 'name' => 'Demo Brand'],
            ['created_by' => $userId]
        );

        $catalog = [
            ['Demo Cola 12oz', 8.50, 12.99],
            ['Demo Chips Family', 6.25, 9.49],
            ['Demo Energy Drink', 11.00, 16.99],
            ['Demo Water Case', 5.10, 7.99],
            ['Demo Candy Mix', 9.40, 14.50],
            ['Demo Lighter Pack', 4.00, 6.99],
        ];

        foreach ($catalog as $i => [$name, $cost, $sell]) {
            $sku = 'DEMO-SKU-'.($i + 1);
            if (Product::where('business_id', $businessId)->where('sku', $sku)->exists()) {
                continue;
            }
            $product = Product::create([
                'name' => $name,
                'business_id' => $businessId,
                'type' => 'single',
                'unit_id' => $unit->id,
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'tax_type' => 'inclusive',
                'enable_stock' => 1,
                'barcode_type' => 'C128',
                'sku' => $sku,
                'created_by' => $userId,
            ]);
            $this->productUtil->createSingleProductVariation(
                $product,
                $sku,
                $cost,
                $cost,
                25,
                $sell,
                $sell
            );
        }

        return Variation::query()
            ->whereHas('product', function ($q) use ($businessId) {
                $q->where('business_id', $businessId)->where('type', 'single');
            })
            ->with('product')
            ->limit(12)
            ->get();
    }

    protected function seedPurchases(int $businessId, int $locationId, int $userId, $suppliers, $variations): void
    {
        $slice = $variations->take(6)->values();
        $statuses = [
            ['received', 'paid', 12],
            ['received', 'partial', 8],
            ['received', 'due', 5],
            ['pending', 'due', 3],
            ['received', 'paid', 18],
        ];

        foreach ($statuses as $i => [$status, $payStatus, $daysAgo]) {
            $lines = $this->pickLines($slice, 3, 20, 80);
            $this->createPurchase(
                $businessId,
                $locationId,
                $userId,
                $suppliers[$i % $suppliers->count()],
                $lines,
                'purchase',
                $status,
                $payStatus,
                Carbon::now()->subDays($daysAgo),
                'DEMO-PO-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)
            );
        }

        $poLines = $this->pickLines($slice, 2, 10, 40);
        $this->createPurchase(
            $businessId,
            $locationId,
            $userId,
            $suppliers[0],
            $poLines,
            'purchase_order',
            'ordered',
            'due',
            Carbon::now()->subDays(2),
            'DEMO-PORD-0001'
        );
        $this->createPurchase(
            $businessId,
            $locationId,
            $userId,
            $suppliers[1 % $suppliers->count()],
            $this->pickLines($slice, 2, 8, 24),
            'purchase_order',
            'pending',
            'due',
            Carbon::now()->subDays(1),
            'DEMO-PORD-0002'
        );
    }

    protected function seedSalesOrders(int $businessId, int $locationId, int $userId, $customers, $variations)
    {
        $slice = $variations->take(6)->values();
        $created = collect();

        $specs = [
            ['ordered', 4],
            ['ordered', 3],
            ['partial', 10],
            ['completed', 16],
        ];

        foreach ($specs as $i => [$status, $daysAgo]) {
            $lines = $this->pickSellLines($slice, 3);
            $so = $this->createSale(
                $businessId,
                $locationId,
                $userId,
                $customers[$i % $customers->count()],
                $lines,
                'sales_order',
                $status,
                'due',
                Carbon::now()->subDays($daysAgo),
                'DEMO-SO-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                false,
                0
            );
            $created->push($so);
        }

        return $created;
    }

    protected function seedSales(int $businessId, int $locationId, int $userId, $customers, $variations, $salesOrders): void
    {
        $slice = $variations->take(8)->values();

        $specs = [
            ['final', 'paid', 1, 0, true],
            ['final', 'paid', 2, 0, false],
            ['final', 'partial', 3, 0, true],
            ['final', 'due', 6, 0, false],
            ['final', 'paid', 0, 0, true],
            ['draft', 'due', 1, 0, false],
        ];

        foreach ($specs as $i => [$status, $pay, $daysAgo, , $pos]) {
            $this->createSale(
                $businessId,
                $locationId,
                $userId,
                $customers[$i % $customers->count()],
                $this->pickSellLines($slice, 3),
                'sell',
                $status,
                $pay,
                Carbon::now()->subDays($daysAgo)->setTime(10 + $i, 15),
                'DEMO-INV-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                $status === 'final' && $pos === false,
                $status === 'final' ? 1 : 0
            );
        }

        $this->createSale(
            $businessId,
            $locationId,
            $userId,
            $customers[0],
            $this->pickSellLines($slice, 2),
            'sell',
            'draft',
            'due',
            Carbon::now()->subDays(1),
            'DEMO-QT-0001',
            true,
            0,
            'quotation'
        );

        // Convert first completed/partial SO into an invoice
        $sourceSo = $salesOrders->first(fn ($t) => in_array($t->status, ['partial', 'completed', 'ordered'], true));
        if ($sourceSo) {
            $lines = [];
            foreach ($sourceSo->sell_lines as $line) {
                $qty = max(1, (int) floor($line->quantity / 2) ?: 1);
                $lines[] = [
                    'product_id' => $line->product_id,
                    'variation_id' => $line->variation_id,
                    'quantity' => $qty,
                    'unit_price' => $line->unit_price,
                    'unit_price_inc_tax' => $line->unit_price_inc_tax,
                ];
            }
            $invoice = $this->createSale(
                $businessId,
                $locationId,
                $userId,
                Contact::find($sourceSo->contact_id),
                $lines,
                'sell',
                'final',
                'paid',
                Carbon::now()->subDays(1),
                'DEMO-INV-SO-0001',
                true,
                0
            );
            $invoice->sales_order_ids = [$sourceSo->id];
            $invoice->save();

            foreach ($sourceSo->sell_lines as $soLine) {
                $invoiced = collect($lines)->firstWhere('variation_id', $soLine->variation_id);
                if ($invoiced) {
                    $soLine->so_quantity_invoiced = $invoiced['quantity'];
                    $soLine->save();
                }
            }
            $sourceSo->status = 'partial';
            $sourceSo->save();
        }
    }

    protected function seedExpenses(int $businessId, int $locationId, int $userId): void
    {
        $rent = ExpenseCategory::firstOrCreate(
            ['business_id' => $businessId, 'name' => 'Demo Rent'],
            []
        );
        $util = ExpenseCategory::firstOrCreate(
            ['business_id' => $businessId, 'name' => 'Demo Utilities'],
            []
        );

        $rows = [
            [$rent->id, 850, 4, 'paid', 'Store rent'],
            [$util->id, 126.40, 2, 'due', 'Electric bill'],
            [$util->id, 64.10, 9, 'paid', 'Internet'],
        ];

        foreach ($rows as $i => [$catId, $amount, $daysAgo, $pay, $note]) {
            $txn = Transaction::create([
                'business_id' => $businessId,
                'location_id' => $locationId,
                'type' => 'expense',
                'status' => 'final',
                'payment_status' => $pay,
                'expense_category_id' => $catId,
                'transaction_date' => Carbon::now()->subDays($daysAgo),
                'total_before_tax' => $amount,
                'final_total' => $amount,
                'tax_amount' => 0,
                'ref_no' => 'DEMO-EXP-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'additional_notes' => self::MARKER.' '.$note,
                'created_by' => $userId,
            ]);
            if ($pay === 'paid') {
                $this->addPayment($txn, $amount, $userId);
            }
        }
    }

    protected function seedStockAdjustment(int $businessId, int $locationId, int $userId, $variations): void
    {
        $v = $variations->first();
        if (! $v) {
            return;
        }
        $qty = 2;
        $unitPrice = (float) $v->dpp_inc_tax;

        $txn = Transaction::create([
            'business_id' => $businessId,
            'location_id' => $locationId,
            'type' => 'stock_adjustment',
            'status' => 'received',
            'adjustment_type' => 'normal',
            'transaction_date' => Carbon::now()->subDays(3),
            'total_before_tax' => $qty * $unitPrice,
            'final_total' => $qty * $unitPrice,
            'ref_no' => 'DEMO-SA-0001',
            'additional_notes' => self::MARKER.' Cycle count correction',
            'created_by' => $userId,
            'payment_status' => 'paid',
        ]);

        StockAdjustmentLine::create([
            'transaction_id' => $txn->id,
            'product_id' => $v->product_id,
            'variation_id' => $v->id,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
        ]);

        $this->productUtil->updateProductQuantity($locationId, $v->product_id, $v->id, 0, $qty, null, false);
    }

    protected function seedReturns(int $businessId, int $locationId, int $userId, $customers, $suppliers, $variations): void
    {
        $v = $variations->first();
        if (! $v) {
            return;
        }

        $sellReturn = $this->createSale(
            $businessId,
            $locationId,
            $userId,
            $customers[0],
            [[
                'product_id' => $v->product_id,
                'variation_id' => $v->id,
                'quantity' => 1,
                'unit_price' => (float) $v->default_sell_price,
                'unit_price_inc_tax' => (float) $v->sell_price_inc_tax,
            ]],
            'sell_return',
            'final',
            'paid',
            Carbon::now()->subDays(2),
            'DEMO-CN-0001',
            true,
            0
        );
        $this->productUtil->updateProductQuantity($locationId, $v->product_id, $v->id, 1, 0, null, false);

        $this->createPurchase(
            $businessId,
            $locationId,
            $userId,
            $suppliers[0],
            [[
                'product_id' => $v->product_id,
                'variation_id' => $v->id,
                'quantity' => 2,
                'pp' => (float) $v->default_purchase_price,
            ]],
            'purchase_return',
            'received',
            'paid',
            Carbon::now()->subDays(7),
            'DEMO-PR-0001'
        );
        unset($sellReturn);
    }

    protected function createPurchase(
        int $businessId,
        int $locationId,
        int $userId,
        Contact $supplier,
        array $lines,
        string $type,
        string $status,
        string $payStatus,
        Carbon $date,
        string $refNo
    ): Transaction {
        $total = 0;
        foreach ($lines as $line) {
            $total += $line['quantity'] * $line['pp'];
        }

        $txn = Transaction::create([
            'business_id' => $businessId,
            'location_id' => $locationId,
            'type' => $type,
            'status' => $status,
            'payment_status' => $payStatus,
            'contact_id' => $supplier->id,
            'ref_no' => $refNo,
            'transaction_date' => $date,
            'total_before_tax' => $total,
            'tax_amount' => 0,
            'final_total' => $total,
            'additional_notes' => self::MARKER.' Demo '.$type,
            'created_by' => $userId,
            'exchange_rate' => 1,
        ]);

        foreach ($lines as $line) {
            PurchaseLine::create([
                'transaction_id' => $txn->id,
                'product_id' => $line['product_id'],
                'variation_id' => $line['variation_id'],
                'quantity' => $line['quantity'],
                'pp_without_discount' => $line['pp'],
                'purchase_price' => $line['pp'],
                'purchase_price_inc_tax' => $line['pp'],
                'item_tax' => 0,
            ]);

            if ($type === 'purchase' && $status === 'received') {
                $this->productUtil->updateProductQuantity(
                    $locationId,
                    $line['product_id'],
                    $line['variation_id'],
                    $line['quantity'],
                    0,
                    null,
                    false
                );
            }
        }

        if ($payStatus === 'paid') {
            $this->addPayment($txn, $total, $userId);
        } elseif ($payStatus === 'partial') {
            $this->addPayment($txn, round($total * 0.4, 2), $userId);
        }

        return $txn;
    }

    protected function createSale(
        int $businessId,
        int $locationId,
        int $userId,
        Contact $customer,
        array $lines,
        string $type,
        string $status,
        string $payStatus,
        Carbon $date,
        string $invoiceNo,
        bool $isDirectSale,
        int $decreaseStock,
        ?string $subStatus = null
    ): Transaction {
        $total = 0;
        foreach ($lines as $line) {
            $total += $line['quantity'] * $line['unit_price_inc_tax'];
        }

        $txn = Transaction::create([
            'business_id' => $businessId,
            'location_id' => $locationId,
            'type' => $type,
            'status' => $status,
            'sub_status' => $subStatus,
            'payment_status' => $payStatus,
            'contact_id' => $customer->id,
            'invoice_no' => $invoiceNo,
            'transaction_date' => $date,
            'total_before_tax' => $total,
            'tax_amount' => 0,
            'discount_type' => 'percentage',
            'discount_amount' => 0,
            'final_total' => $total,
            'additional_notes' => self::MARKER.' Demo '.$type,
            'created_by' => $userId,
            'is_direct_sale' => $isDirectSale ? 1 : 0,
            'exchange_rate' => 1,
            'shipping_status' => $type === 'sales_order' ? 'ordered' : null,
            'source' => $type === 'sales_order' ? 'web' : null,
        ]);

        foreach ($lines as $line) {
            TransactionSellLine::create([
                'transaction_id' => $txn->id,
                'product_id' => $line['product_id'],
                'variation_id' => $line['variation_id'],
                'quantity' => $line['quantity'],
                'unit_price_before_discount' => $line['unit_price'],
                'unit_price' => $line['unit_price'],
                'line_discount_type' => 'fixed',
                'line_discount_amount' => 0,
                'item_tax' => 0,
                'unit_price_inc_tax' => $line['unit_price_inc_tax'],
            ]);

            if ($decreaseStock && $status === 'final' && $type === 'sell') {
                try {
                    $this->productUtil->decreaseProductQuantity(
                        $line['product_id'],
                        $line['variation_id'],
                        $locationId,
                        $line['quantity']
                    );
                } catch (\Throwable $e) {
                    // Demo seed should not fail if stock mapping is strict
                }
            }
        }

        if ($type === 'sell' && $status === 'final') {
            if ($payStatus === 'paid') {
                $this->addPayment($txn, $total, $userId);
            } elseif ($payStatus === 'partial') {
                $this->addPayment($txn, round($total * 0.5, 2), $userId);
            }
        }

        $txn->load('sell_lines');

        return $txn;
    }

    protected function addPayment(Transaction $txn, float $amount, int $userId): void
    {
        TransactionPayment::create([
            'transaction_id' => $txn->id,
            'business_id' => $txn->business_id,
            'amount' => $amount,
            'method' => 'cash',
            'paid_on' => $txn->transaction_date,
            'created_by' => $userId,
            'payment_for' => $txn->contact_id,
            'note' => self::MARKER,
        ]);
    }

    protected function pickLines($variations, int $count, int $minQty, int $maxQty): array
    {
        $lines = [];
        foreach ($variations->take($count) as $v) {
            $qty = random_int($minQty, $maxQty);
            $pp = (float) ($v->dpp_inc_tax ?: $v->default_purchase_price ?: 5);
            $lines[] = [
                'product_id' => $v->product_id,
                'variation_id' => $v->id,
                'quantity' => $qty,
                'pp' => $pp,
            ];
        }

        return $lines;
    }

    protected function pickSellLines($variations, int $count): array
    {
        $lines = [];
        foreach ($variations->take($count) as $v) {
            $qty = random_int(1, 6);
            $sell = (float) ($v->sell_price_inc_tax ?: $v->default_sell_price ?: 9);
            $lines[] = [
                'product_id' => $v->product_id,
                'variation_id' => $v->id,
                'quantity' => $qty,
                'unit_price' => $sell,
                'unit_price_inc_tax' => $sell,
            ];
        }

        return $lines;
    }

    protected function counts(int $businessId): array
    {
        $q = fn ($type) => Transaction::where('business_id', $businessId)
            ->where('type', $type)
            ->where('additional_notes', 'like', self::MARKER.'%')
            ->count();

        return [
            'Purchases' => $q('purchase'),
            'Purchase orders' => $q('purchase_order'),
            'Sales orders' => $q('sales_order'),
            'Sales' => $q('sell'),
            'Sell returns' => $q('sell_return'),
            'Purchase returns' => $q('purchase_return'),
            'Expenses' => $q('expense'),
            'Stock adjustments' => $q('stock_adjustment'),
        ];
    }

    protected function log(?callable $log, string $message): void
    {
        if ($log) {
            $log($message);
        }
    }
}

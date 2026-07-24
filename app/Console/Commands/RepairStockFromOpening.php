<?php

namespace App\Console\Commands;

use App\BusinessLocation;
use App\Product;
use App\Variation;
use App\VariationLocationDetails;
use DB;
use Illuminate\Console\Command;

class RepairStockFromOpening extends Command
{
    protected $signature = 'stock:repair-from-opening
                            {--business_id=1 : Business ID}
                            {--location_id= : Location ID (default: first location)}
                            {--dry-run : Show what would change without writing}';

    protected $description = 'Restore on-hand qty to QuickBooks opening-stock quantities (undo double-count when historical sales also decreased stock)';

    public function handle(): int
    {
        $businessId = (int) $this->option('business_id');
        $locationId = $this->option('location_id')
            ? (int) $this->option('location_id')
            : (int) BusinessLocation::where('business_id', $businessId)->value('id');

        if (! $locationId) {
            $this->error('No location found.');

            return self::FAILURE;
        }

        $this->info("Repairing stock for business #{$businessId}, location #{$locationId}");
        $this->line('Setting qty_available = opening stock qty (from opening_stock transactions).');
        $this->line('Historical imported sales already reflected in QB QOH — stock was decreased twice.');

        // Opening qty per variation at this location
        $opening = DB::table('purchase_lines as pl')
            ->join('transactions as t', 't.id', '=', 'pl.transaction_id')
            ->where('t.business_id', $businessId)
            ->where('t.location_id', $locationId)
            ->where('t.type', 'opening_stock')
            ->selectRaw('pl.variation_id, pl.product_id, SUM(pl.quantity) as opening_qty')
            ->groupBy('pl.variation_id', 'pl.product_id')
            ->get()
            ->keyBy('variation_id');

        if ($opening->isEmpty()) {
            $this->warn('No opening stock found.');

            return self::SUCCESS;
        }

        $updated = 0;
        $created = 0;
        $unchanged = 0;
        $flooredNeg = 0;
        $bar = $this->output->createProgressBar($opening->count());
        $bar->start();

        DB::beginTransaction();
        try {
            foreach ($opening as $variationId => $row) {
                $bar->advance();
                $openingQty = (float) $row->opening_qty;
                // Physical inventory for MSA/POS: never keep QB negative QOH as on-hand.
                $targetQty = max(0.0, $openingQty);
                if ($openingQty < 0) {
                    $flooredNeg++;
                }

                $variation = Variation::find($variationId);
                if (! $variation) {
                    continue;
                }

                $vld = VariationLocationDetails::where('variation_id', $variationId)
                    ->where('location_id', $locationId)
                    ->first();

                $current = $vld ? (float) $vld->qty_available : null;
                if ($current !== null && abs($current - $targetQty) < 0.0001) {
                    $unchanged++;
                    continue;
                }

                if ($this->option('dry-run')) {
                    if ($updated + $created < 8) {
                        $sku = $variation->sub_sku;
                        $this->newLine();
                        $this->line("  SKU {$sku}: ".($current ?? 'none')." → {$targetQty} (opening={$openingQty})");
                    }
                    if ($vld) {
                        $updated++;
                    } else {
                        $created++;
                    }
                    continue;
                }

                if ($vld) {
                    $vld->qty_available = $targetQty;
                    $vld->save();
                    $updated++;
                } else {
                    VariationLocationDetails::create([
                        'product_id' => $row->product_id,
                        'product_variation_id' => $variation->product_variation_id,
                        'variation_id' => $variationId,
                        'location_id' => $locationId,
                        'qty_available' => $targetQty,
                    ]);
                    $created++;
                }

                // Ensure product is stock-managed if it had opening stock
                Product::where('id', $row->product_id)
                    ->where('enable_stock', 0)
                    ->update(['enable_stock' => 1]);
            }

            if ($this->option('dry-run')) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $bar->finish();
            $this->newLine();
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $bar->finish();
        $this->newLine(2);
        $prefix = $this->option('dry-run') ? '[dry-run] ' : '';
        $this->info("{$prefix}Updated: {$updated}, created: {$created}, unchanged: {$unchanged}");
        $this->info("{$prefix}Negative QB QOH floored to 0: {$flooredNeg}");

        if (! $this->option('dry-run')) {
            $pos = VariationLocationDetails::where('location_id', $locationId)->where('qty_available', '>', 0)->count();
            $sum = VariationLocationDetails::where('location_id', $locationId)->where('qty_available', '>', 0)->sum('qty_available');
            $this->info("Location now has {$pos} SKUs with stock > 0 (sum={$sum}).");
        }

        return self::SUCCESS;
    }
}

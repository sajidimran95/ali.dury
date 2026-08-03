<?php

namespace App\Console\Commands;

use App\Business;
use App\Services\Msa\MsaSetupService;
use Illuminate\Console\Command;

class SetupMsaTobaccoReport extends Command
{
    protected $signature = 'msa:setup
                            {--business_id= : Optional business id (default: all)}
                            {--sales : Force (re)seed Nashville retailer sales for current MSA week}
                            {--repair : Only re-run MSA product repair (categories/units/lighters)}';

    protected $description = 'Create Tobacco MSA categories, repair catalog, and optionally seed Nashville sales for MULTICAT export';

    public function handle(MsaSetupService $msaSetup): int
    {
        $query = Business::query();
        if ($this->option('business_id')) {
            $query->where('id', $this->option('business_id'));
        }

        $businesses = $query->get();
        if ($businesses->isEmpty()) {
            $this->error('No business found.');

            return self::FAILURE;
        }

        foreach ($businesses as $business) {
            $this->info("Setting up MSA for business #{$business->id} ({$business->name})");

            if ($this->option('repair')) {
                $parentId = $msaSetup->ensureCategories((int) $business->id);
                $msaSetup->ensureTobaccoProductDefaults((int) $business->id, $parentId, null);
                $this->line("  + repaired catalog under Tobacco id={$parentId}");
                continue;
            }

            $parentId = $msaSetup->setupForBusiness($business, true);
            $this->line("  + Tobacco category id={$parentId}");

            if ($this->option('sales')) {
                // Clear prior demo invoices for current week tag so we can reseed
                $weekEnd = now()->isSaturday()
                    ? now()->startOfDay()
                    : now()->previous(\Carbon\Carbon::SATURDAY)->startOfDay();
                $prefix = 'MSA-DEMO-'.$weekEnd->format('Ymd').'-';
                $old = \App\Transaction::where('business_id', $business->id)
                    ->where('type', 'sell')
                    ->where('invoice_no', 'like', $prefix.'%')
                    ->get();
                foreach ($old as $txn) {
                    \App\TransactionSellLine::where('transaction_id', $txn->id)->delete();
                    try {
                        \App\TransactionPayment::where('transaction_id', $txn->id)->delete();
                    } catch (\Throwable $e) {
                    }
                    $txn->delete();
                }

                $locationId = (int) (\App\BusinessLocation::where('business_id', $business->id)->orderBy('id')->value('id') ?: 1);
                $userId = (int) (\App\User::where('business_id', $business->id)->value('id') ?: 1);
                $count = $msaSetup->ensureDemoWeekSales($business, $locationId, $userId, $parentId);
                $this->line("  + seeded {$count} sell invoice(s) for week ending ".$weekEnd->toDateString());
            }

            $this->line('  done.');
        }

        $this->info('MSA Tobacco setup complete. Export ONE file from Reports → MSA Tobacco Report (Tobacco category, distributor 17033752).');

        return self::SUCCESS;
    }
}

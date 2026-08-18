<?php

namespace App\Console\Commands;

use App\Services\Demo\DemoOperationalDataSeeder;
use Illuminate\Console\Command;

class SeedDemoData extends Command
{
    protected $signature = 'pos:seedDemoData
                            {--business=1 : Business ID to fill}
                            {--force : Remove previous DEMO seed for this business, then re-insert}';

    protected $description = 'Insert demo purchases, sales orders, sales, expenses, and related lists without wiping existing data';

    public function handle(DemoOperationalDataSeeder $seeder): int
    {
        $businessId = (int) $this->option('business');
        $force = (bool) $this->option('force');

        $this->info("Seeding demo operational data for business #{$businessId}...");

        try {
            $summary = $seeder->seed($businessId, $force, function ($line) {
                $this->line('  '.$line);
            });
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Demo data ready. Open these lists to verify:');
        foreach ($summary as $label => $count) {
            $this->line(sprintf('  %-22s %s', $label.':', $count));
        }

        return self::SUCCESS;
    }
}

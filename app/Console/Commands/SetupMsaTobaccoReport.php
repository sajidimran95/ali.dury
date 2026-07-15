<?php

namespace App\Console\Commands;

use App\Business;
use App\Services\Msa\MsaSetupService;
use Illuminate\Console\Command;

class SetupMsaTobaccoReport extends Command
{
    protected $signature = 'msa:setup {--business_id= : Optional business id (default: all)}';

    protected $description = 'Create Tobacco MSA categories and custom field labels for MULTICAT export';

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
            $parentId = $msaSetup->setupForBusiness($business);
            $this->line("  + Tobacco category id={$parentId}");
            $this->line('  done.');
        }

        $this->info('MSA Tobacco setup complete.');

        return self::SUCCESS;
    }
}

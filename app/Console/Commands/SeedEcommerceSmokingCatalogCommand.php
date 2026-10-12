<?php

namespace App\Console\Commands;

use Database\Seeders\EcommerceSmokingCatalogSeeder;
use Illuminate\Console\Command;

class SeedEcommerceSmokingCatalogCommand extends Command
{
    protected $signature = 'ecommerce:seed-smoking';

    protected $description = 'Hide existing POS items from the storefront and seed 300 vape/cigar/smoking ecommerce products';

    public function handle(): int
    {
        $seeder = new EcommerceSmokingCatalogSeeder;
        $seeder->setCommand($this);
        $seeder->run();

        return self::SUCCESS;
    }
}

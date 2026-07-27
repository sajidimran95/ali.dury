<?php

namespace App\Console\Commands;

use App\SellingPriceGroup;
use Illuminate\Console\Command;

class SeedPriceGroupInventory extends Command
{
    protected $signature = 'price-groups:seed-inventory
                            {--business_id=1 : Business ID}
                            {--group_id= : Seed only this selling price group ID}';

    protected $description = 'Seed missing variation prices for selling price groups from default sell prices (full shared inventory)';

    public function handle(): int
    {
        $businessId = (int) $this->option('business_id');
        $groupId = $this->option('group_id');

        $query = SellingPriceGroup::where('business_id', $businessId);
        if (! empty($groupId)) {
            $query->where('id', (int) $groupId);
        }

        $groups = $query->get();
        if ($groups->isEmpty()) {
            $this->error('No selling price groups found.');

            return self::FAILURE;
        }

        foreach ($groups as $group) {
            $seeded = SellingPriceGroup::seedAllInventoryPrices($businessId, (int) $group->id);
            $this->info("Group [{$group->id}] {$group->name}: seeded {$seeded} price row(s).");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}

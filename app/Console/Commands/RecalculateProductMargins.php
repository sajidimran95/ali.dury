<?php

namespace App\Console\Commands;

use App\Variation;
use Illuminate\Console\Command;

class RecalculateProductMargins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:recalculate-margins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate profit margins for all products based on their current purchase and selling prices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to recalculate product margins...');
        
        $variations = Variation::all();
        $updated = 0;
        $bar = $this->output->createProgressBar(count($variations));
        
        foreach ($variations as $variation) {
            if ($variation->default_purchase_price > 0) {
                $profit = $variation->default_sell_price - $variation->default_purchase_price;
                $margin = ($profit / $variation->default_purchase_price) * 100;
                
                $variation->profit_percent = round($margin, 4);
                $variation->save();
                $updated++;
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        $this->info("Successfully recalculated margins for {$updated} products!");
        
        return 0;
    }
}

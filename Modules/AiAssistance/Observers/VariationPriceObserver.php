<?php

namespace Modules\AiAssistance\Observers;

use App\Variation;
use Modules\AiAssistance\Services\AdminAlertService;
use Modules\AiAssistance\Services\PoCostSyncService;

class VariationPriceObserver
{
    public function updated(Variation $variation): void
    {
        if (PoCostSyncService::$syncing) {
            return;
        }

        try {
            $row = [];
            $sellChanged = $this->changed($variation, 'default_sell_price');
            $costChanged = $this->changed($variation, 'default_purchase_price');
            if (! $sellChanged && ! $costChanged) {
                return;
            }

            $product = $variation->product()->select('id', 'name', 'business_id')->first();
            if (! $product) {
                return;
            }

            $row = [
                'product' => $product->name,
                'product_id' => $product->id,
                'sku' => $variation->sub_sku,
            ];
            if ($sellChanged) {
                $row['old_sell'] = (float) $variation->getOriginal('default_sell_price');
                $row['new_sell'] = (float) $variation->default_sell_price;
            }
            if ($costChanged) {
                $row['old_cost'] = (float) $variation->getOriginal('default_purchase_price');
                $row['new_cost'] = (float) $variation->default_purchase_price;
            }

            $source = app()->runningInConsole() ? 'console' : ('/'.ltrim(request()->path(), '/'));

            app(AdminAlertService::class)->queue(
                (int) $product->business_id,
                AdminAlertService::TYPE_PRICE_EDIT,
                'price_edit',
                $row,
                [
                    'source' => $source,
                    'user' => optional(auth()->user())->user_full_name ?: optional(auth()->user())->username,
                ]
            );
        } catch (\Throwable $e) {
            \Log::warning('JapsAI price alert skipped: '.$e->getMessage());
        }
    }

    protected function changed(Variation $variation, string $field): bool
    {
        if (! $variation->wasChanged($field)) {
            return false;
        }

        return round((float) $variation->getOriginal($field), 4) !== round((float) $variation->{$field}, 4);
    }
}

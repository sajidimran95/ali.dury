<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VariationGroupPrice extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Accessor that Calculates the price of price group of a product based on price_type of it.
     */
    public function getCalculatedPriceAttribute()
    {
        if (! isset($this->price_type) || $this->price_type === 'fixed') {
            $price = $this->price_inc_tax;
        } else {
            $variation = Variation::find($this->variation_id);
            $utils = new \App\Utils\Util();

            if ($this->price_type === 'percentage_adjustment') {
                $adjustment = $utils->calc_percentage($variation->sell_price_inc_tax, $this->price_inc_tax);
                $price = $variation->sell_price_inc_tax + $adjustment;
            } else {
                // Keep legacy "percentage" records as a percentage of the base price.
                $price = $utils->calc_percentage($variation->sell_price_inc_tax, $this->price_inc_tax);
            }
        }

        return $price;
    }
}

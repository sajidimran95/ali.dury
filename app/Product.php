<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected $appends = ['image_url'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'sub_unit_ids' => 'array',
        'ecom_gallery' => 'array',
        'ecom_is_featured' => 'boolean',
        'ecom_is_new_arrival' => 'boolean',
    ];

    public function scopeForEcommerce($query)
    {
        return $query->whereIn('products.sales_channel', ['pos_ecommerce', 'ecommerce_only']);
    }

    public static function storefrontQuery(int $businessId)
    {
        $base = static::query()->where('business_id', $businessId)->active();
        if ((clone $base)->forEcommerce()->exists()) {
            return $base->forEcommerce();
        }

        return $base;
    }

    public function scopeForPos($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('products.sales_channel')
                ->orWhereIn('products.sales_channel', ['pos_only', 'pos_ecommerce']);
        });
    }

    public function getEcomPriceAttribute()
    {
        if (! is_null($this->ecom_discount_price) && $this->ecom_discount_price > 0) {
            return (float) $this->ecom_discount_price;
        }
        if (! is_null($this->ecom_online_price) && $this->ecom_online_price > 0) {
            return (float) $this->ecom_online_price;
        }

        $variation = $this->variations->first();

        return $variation ? (float) $variation->sell_price_inc_tax : 0;
    }

    /**
     * Get the products image.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if (! empty($this->image)) {
            $image_path = public_path('uploads/'.config('constants.product_img_path').'/'.$this->image);
            if (is_file($image_path)) {
                return asset('/uploads/'.config('constants.product_img_path').'/'.rawurlencode($this->image));
            }
        }

        return asset('/img/default.png');
    }

    /**
     * Get the products image path.
     *
     * @return string
     */
    public function getImagePathAttribute()
    {
        if (! empty($this->image)) {
            $image_path = public_path('uploads').'/'.config('constants.product_img_path').'/'.$this->image;
        } else {
            $image_path = null;
        }

        return $image_path;
    }

    public function product_variations()
    {
        return $this->hasMany(\App\ProductVariation::class);
    }

    /**
     * Get the brand associated with the product.
     */
    public function brand()
    {
        return $this->belongsTo(\App\Brands::class);
    }

    /**
     * Get the unit associated with the product.
     */
    public function unit()
    {
        return $this->belongsTo(\App\Unit::class);
    }

    /**
     * Get the unit associated with the product.
     */
    public function second_unit()
    {
        return $this->belongsTo(\App\Unit::class, 'secondary_unit_id');
    }

    /**
     * Get category associated with the product.
     */
    public function category()
    {
        return $this->belongsTo(\App\Category::class);
    }

    /**
     * Get sub-category associated with the product.
     */
    public function sub_category()
    {
        return $this->belongsTo(\App\Category::class, 'sub_category_id', 'id');
    }

    /**
     * Get child category (third level) associated with the product.
     */
    public function child_category()
    {
        return $this->belongsTo(\App\Category::class, 'child_category_id', 'id');
    }

    /**
     * Get the tax associated with the product.
     */
    public function product_tax()
    {
        return $this->belongsTo(\App\TaxRate::class, 'tax', 'id');
    }

    /**
     * Get the variations associated with the product.
     */
    public function variations()
    {
        return $this->hasMany(\App\Variation::class);
    }

    public function barcodes()
    {
        return $this->hasMany(\App\ProductBarcode::class);
    }

    /**
     * If product type is modifier get products associated with it.
     */
    public function modifier_products()
    {
        return $this->belongsToMany(\App\Product::class, 'res_product_modifier_sets', 'modifier_set_id', 'product_id');
    }

    /**
     * If product type is modifier get products associated with it.
     */
    public function modifier_sets()
    {
        return $this->belongsToMany(\App\Product::class, 'res_product_modifier_sets', 'product_id', 'modifier_set_id');
    }

    /**
     * Get the purchases associated with the product.
     */
    public function purchase_lines()
    {
        return $this->hasMany(\App\PurchaseLine::class);
    }

    /**
     * Scope a query to only include active products.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('products.is_inactive', 0);
    }

    /**
     * Scope a query to only include inactive products.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('products.is_inactive', 1);
    }

    /**
     * Scope a query to only include products for sales.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProductForSales($query)
    {
        return $query->where('not_for_selling', 0);
    }

    /**
     * Scope a query to only include products not for sales.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProductNotForSales($query)
    {
        return $query->where('not_for_selling', 1);
    }

    public function product_locations()
    {
        return $this->belongsToMany(\App\BusinessLocation::class, 'product_locations', 'product_id', 'location_id');
    }

    /**
     * Scope a query to only include products available for a location.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForLocation($query, $location_id)
    {
        return $query->where(function ($q) use ($location_id) {
            $q->whereHas('product_locations', function ($query) use ($location_id) {
                $query->where('product_locations.location_id', $location_id);
            });
        });
    }

    /**
     * Get warranty associated with the product.
     */
    public function warranty()
    {
        return $this->belongsTo(\App\Warranty::class);
    }

    public function media()
    {
        return $this->morphMany(\App\Media::class, 'model');
    }

    public function rack_details()
    {
        return $this->hasMany(\App\ProductRack::class);
    }
}

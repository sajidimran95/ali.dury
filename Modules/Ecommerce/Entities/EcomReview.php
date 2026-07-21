<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class EcomReview extends Model
{
    protected $table = 'ecom_reviews';

    protected $guarded = ['id'];

    protected $casts = [
        'images' => 'array',
        'rating' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrossStoreStockLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'qty_delta' => 'float',
        'qty_after' => 'float',
    ];
}

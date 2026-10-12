<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Modules\Ecommerce\Support\StaticCatalog;
use Modules\Ecommerce\Support\StaticCart;

class CollectionFrontController extends Controller
{
    public function show($slug)
    {
        $collection = StaticCatalog::collection($slug);
        abort_unless($collection, 404);
        $cart_totals = StaticCart::totals();

        return view('ecommerce::front.shop.collection', compact('collection', 'cart_totals'));
    }
}

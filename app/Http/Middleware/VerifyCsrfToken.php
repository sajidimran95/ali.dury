<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

class VerifyCsrfToken extends PreventRequestForgery
{
    protected $except = [
        '/api/ecom/customers',
        '/api/ecom/orders',
        '/webhook/*',
        'shop',
        'shop/*',
        '*/shop',
        '*/shop/*',
        'cart',
        'cart/*',
        '*/cart',
        '*/cart/*',
        'checkout',
        '*/checkout',
        'contact',
        'subscribe',
        'forgot-password',
        'reset-password',
        'reset-password/*',
        'account/apply',
        'stock-alert',
        'brand-request',
        'product/*',
        'category/*',
        'promotions',
        'promotions/*',
        'page/*',
        'new-arrivals',
        'new-arrivals/*',
        'search',
        'collections/*',
        'wishlist',
        'review',
        'quick-order',
    ];

    public function handle($request, Closure $next)
    {
        if (SeparateAppSession::isStorefront($request)) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}

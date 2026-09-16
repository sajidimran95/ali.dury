<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            if ($request->is('sale') || $request->is('sale/*')) {
                return route('sale.login');
            }

            if ($request->is('customer') || $request->is('customer/*')) {
                return route('customer.login');
            }

            if ($request->is('delivery') || $request->is('delivery/*')) {
                return route('delivery.app.login');
            }

            $shop = trim((string) config('ecommerce.route_prefix', 'shop'), '/');
            if ($shop !== '' && ($request->is($shop) || $request->is($shop.'/*'))) {
                return route('ecommerce.login');
            }

            return route('login');
        }
    }
}

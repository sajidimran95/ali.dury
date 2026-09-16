<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Separate session cookies for Admin / Sale / Customer on the same browser.
 * Must run before StartSession.
 */
class SeparateAppSession
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('customer') || $request->is('customer/*')) {
            config([
                'session.cookie' => 'japs_customer_session',
            ]);
        } elseif ($request->is('sale') || $request->is('sale/*')) {
            config([
                'session.cookie' => 'japs_sale_session',
            ]);
        } elseif ($request->is('delivery') || $request->is('delivery/*')) {
            config([
                'session.cookie' => 'japs_delivery_session',
            ]);
        } else {
            // Admin / main system
            config([
                'session.cookie' => 'japs_admin_session',
            ]);
        }

        return $next($request);
    }
}

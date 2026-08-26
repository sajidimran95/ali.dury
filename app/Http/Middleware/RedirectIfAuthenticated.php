<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Keep each app on its own login (Admin / Sale / Customer).
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (! Auth::guard($guard)->check()) {
                continue;
            }

            // Sale App login — only if THIS guard is sale
            if ($guard === 'sale') {
                return redirect()->route('sale.home');
            }

            // Customer App login
            if ($guard === 'customer') {
                return redirect()->route('customer.home');
            }

            // Admin (web / default) — never send Sale/Customer traffic to Admin from here
            if ($request->is('sale') || $request->is('sale/*')) {
                return redirect()->route('sale.login');
            }
            if ($request->is('customer') || $request->is('customer/*')) {
                return redirect()->route('customer.login');
            }

            return redirect(RouteServiceProvider::HOME);
        }

        return $next($request);
    }
}

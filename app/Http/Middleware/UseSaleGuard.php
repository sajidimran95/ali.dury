<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Force Sale App requests to use only the "sale" guard (never Admin "web").
 */
class UseSaleGuard
{
    public function handle(Request $request, Closure $next)
    {
        Auth::shouldUse('sale');

        return $next($request);
    }
}

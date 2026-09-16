<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UseDeliveryGuard
{
    public function handle(Request $request, Closure $next)
    {
        Auth::shouldUse('delivery');

        return $next($request);
    }
}

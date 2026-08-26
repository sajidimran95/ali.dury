<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Sale\SaleRepGate;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Sale App only — never uses Admin (web) user, never redirects to Admin /home.
 */
class EnsureSaleRepAccess
{
    public function handle(Request $request, Closure $next)
    {
        // Strict: only the sale guard (do NOT fall back to web/admin)
        $user = Auth::guard('sale')->user();

        if (! $user) {
            $request->session()->forget('url.intended');

            return redirect()->route('sale.login');
        }

        if (! SaleRepGate::allows($user)) {
            $msg = SaleRepGate::denyMessage($user);

            Auth::guard('sale')->logout();
            $request->session()->forget('url.intended');

            // Stay in Sales App login — do not touch Admin session / do not go to /home
            return redirect()->route('sale.login')
                ->with('status', ['success' => 0, 'msg' => $msg]);
        }

        return $next($request);
    }
}

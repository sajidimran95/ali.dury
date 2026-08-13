<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Sale\SaleRepGate;
use Closure;
use Illuminate\Http\Request;

/**
 * Restricts /sale portal to sales representatives only (not Admin / other staff without sell access).
 */
class EnsureSaleRepAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('sale.login'));
        }

        if (! SaleRepGate::allows($user)) {
            $msg = SaleRepGate::denyMessage($user);

            // Admin already using main system — don't kill their session
            if (SaleRepGate::isAdmin($user)) {
                return redirect('/home')
                    ->with('status', ['success' => 0, 'msg' => $msg]);
            }

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('sale.login')
                ->with('status', ['success' => 0, 'msg' => $msg]);
        }

        return $next($request);
    }
}

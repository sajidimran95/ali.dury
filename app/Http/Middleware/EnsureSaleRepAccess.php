<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Restricts /sale portal to staff users who can create sales.
 * Does not alter admin or POS behaviour.
 */
class EnsureSaleRepAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('sale.login'));
        }

        if ($user->user_type !== 'user' || empty($user->allow_login) || $user->status !== 'active') {
            auth()->logout();

            return redirect()->route('sale.login')
                ->with('status', ['success' => 0, 'msg' => __('lang_v1.login_not_allowed')]);
        }

        // Same sell permissions as the main system (role-based)
        $canSell = $user->can('sell.create')
            || $user->can('direct_sell.access')
            || $user->can('direct_sell.view')
            || $user->can('view_own_sell_only');

        if (! $canSell) {
            auth()->logout();

            return redirect()->route('sale.login')
                ->with('status', ['success' => 0, 'msg' => 'Your account does not have sales access.']);
        }

        return $next($request);
    }
}

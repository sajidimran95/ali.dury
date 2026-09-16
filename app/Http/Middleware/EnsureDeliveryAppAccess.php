<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureDeliveryAppAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('delivery') ?: $request->user();
        if (! $user || ! self::allows($user)) {
            if ($request->expectsJson()) {
                abort(403);
            }

            return redirect()->route('delivery.app.login')
                ->with('status', ['success' => 0, 'msg' => self::denyMessage($user)]);
        }

        return $next($request);
    }

    public static function allows($user): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->status !== 'active' || ! $user->allow_login) {
            return false;
        }
        if ($user->can('delivery.driver.view') || $user->can('delivery.driver.edit') || $user->can('delivery.manage')) {
            return true;
        }

        return $user->roles->contains(function ($role) {
            return stripos((string) $role->name, 'Delivery#') === 0;
        });
    }

    public static function denyMessage($user): string
    {
        if (! $user) {
            return 'Please log in with a delivery account.';
        }

        return 'This account is not allowed to use the Delivery app.';
    }
}

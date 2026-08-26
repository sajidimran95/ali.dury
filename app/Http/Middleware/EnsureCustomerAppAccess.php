<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCustomerAppAccess
{
    /**
     * Ensure the logged-in contact may use the customer app.
     */
    public function handle(Request $request, Closure $next)
    {
        $contact = Auth::guard('customer')->user();

        if (! $contact) {
            return redirect()->route('customer.login');
        }

        if ((int) ($contact->allow_customer_app ?? 0) !== 1 || empty($contact->password)) {
            Auth::guard('customer')->logout();

            return redirect()
                ->route('customer.login')
                ->with('error', 'Customer app access is not enabled for this account.');
        }

        $type = (string) ($contact->type ?? '');
        if (! in_array($type, ['customer', 'both'], true)) {
            Auth::guard('customer')->logout();

            return redirect()
                ->route('customer.login')
                ->with('error', 'Only customers can use this app.');
        }

        return $next($request);
    }
}

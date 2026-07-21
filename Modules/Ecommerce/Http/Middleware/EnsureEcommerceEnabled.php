<?php

namespace Modules\Ecommerce\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\EcommerceSetting;

class EnsureEcommerceEnabled
{
    public function handle(Request $request, Closure $next)
    {
        $business_id = $this->resolveBusinessId($request);
        $settings = EcommerceSetting::forBusiness($business_id);

        if (! $settings || ! $settings->is_enabled) {
            abort(404);
        }

        $request->attributes->set('ecommerce_business_id', $business_id);
        $request->attributes->set('ecommerce_settings', $settings);
        view()->share('ecom_settings', $settings);
        view()->share('ecom_business_id', $business_id);
        view()->share('cart_totals', ['count' => 0, 'subtotal' => 0, 'total' => 0, 'shipping' => 0, 'tax' => 0, 'discount' => 0]);

        return $next($request);
    }

    protected function resolveBusinessId(Request $request): int
    {
        if ($request->session()->has('user.business_id')) {
            return (int) $request->session()->get('user.business_id');
        }

        // Default to first business with ecommerce enabled, else first business
        $enabled = EcommerceSetting::where('is_enabled', 1)->value('business_id');
        if ($enabled) {
            return (int) $enabled;
        }

        return (int) (\App\Business::query()->value('id') ?? 1);
    }
}

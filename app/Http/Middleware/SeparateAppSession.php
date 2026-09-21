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
            config(['session.cookie' => 'japs_customer_session']);
        } elseif ($request->is('sale') || $request->is('sale/*')) {
            config(['session.cookie' => 'japs_sale_session']);
        } elseif ($request->is('delivery') || $request->is('delivery/*')) {
            config(['session.cookie' => 'japs_delivery_session']);
        } elseif (self::isStorefront($request)) {
            config([
                'session.cookie' => 'japs_ecom_session',
                'session.secure' => $request->isSecure(),
                'session.same_site' => 'lax',
                'session.domain' => null,
            ]);
        } else {
            config(['session.cookie' => 'japs_admin_session']);
        }

        return $next($request);
    }

    public static function isStorefront(Request $request): bool
    {
        // POS dashboard is /home — never treat it as the shop.
        if ($request->is('home') || $request->is('home/*')) {
            return false;
        }

        $path = trim($request->path(), '/');
        $prefix = trim((string) config('ecommerce.route_prefix', 'shop'), '/');
        if ($prefix !== '' && (
            $path === $prefix
            || str_starts_with($path, $prefix.'/')
            || str_contains($path, '/'.$prefix.'/')
            || $request->is($prefix)
            || $request->is($prefix.'/*')
            || $request->is('*/'.$prefix)
            || $request->is('*/'.$prefix.'/*')
        )) {
            return true;
        }

        return $request->is('/')
            || $request->is('product/*')
            || $request->is('category/*')
            || $request->is('collections/*')
            || $request->is('cart')
            || $request->is('cart/*')
            || $request->is('checkout')
            || $request->is('promotions')
            || $request->is('promotions/*')
            || $request->is('page/*')
            || $request->is('contact')
            || $request->is('subscribe')
            || $request->is('search')
            || $request->is('categories')
            || $request->is('about')
            || $request->is('faq')
            || $request->is('terms')
            || $request->is('privacy')
            || $request->is('shipping')
            || $request->is('returns')
            || $request->is('compliance')
            || $request->is('wholesale-agreement')
            || $request->is('new-arrivals')
            || $request->is('new-arrivals/*')
            || $request->is('forgot-password')
            || $request->is('reset-password')
            || $request->is('reset-password/*')
            || $request->is('account/apply')
            || $request->is('ecom-pwa')
            || $request->is('ecom-pwa/*');
    }
}

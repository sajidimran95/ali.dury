<?php

namespace App\Http\Middleware;

use App\Business;
use App\Contact;
use App\Http\Controllers\Sale\SaleRepGate;
use App\MobileAppToken;
use App\User;
use App\Utils\BusinessUtil;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

/**
 * Bearer-token auth for the native apps (/api/v1/{sale|delivery|customer}/*).
 *
 * The PWA controllers read business / user / location data from the session,
 * so every API request gets a fresh in-memory (array) session seeded exactly like
 * SetSessionData does for the PWA. Per-device values the PWA keeps in the session
 * (default location) are persisted on the token row between requests.
 *
 * Usage: ->middleware('mobile.auth:sale') | 'mobile.auth:delivery' | 'mobile.auth:customer'
 */
class MobileApiAuth
{
    /** Session keys that survive between API calls (stored in mobile_app_tokens.meta). */
    protected const PERSISTED_KEYS = [
        'user.default_location_id',
        'customer.default_location_id',
    ];

    public function handle(Request $request, Closure $next, string $app)
    {
        $request->headers->set('Accept', 'application/json');

        $token = MobileAppToken::findValid($request->bearerToken(), $app);
        if (! $token) {
            return self::deny('Unauthenticated. Please log in again.', 401);
        }

        $owner = $token->tokenable;
        $denied = $owner ? self::denyReason($app, $owner) : 'Account not found.';
        if ($denied !== null) {
            $token->delete();

            return self::deny($denied, 403);
        }

        $this->startArraySession($request, $token, $app, $owner);

        if ($app === MobileAppToken::APP_CUSTOMER) {
            // Same as the PWA: default guard stays "web" (no staff user), contact lives on "customer".
            Auth::guard('customer')->setUser($owner);
        } else {
            Auth::shouldUse($app);
            Auth::guard($app)->setUser($owner);
        }

        $request->attributes->set('mobile_token', $token);

        if (! $token->last_used_at || $token->last_used_at->lt(now()->subMinute())) {
            $token->forceFill(['last_used_at' => now()])->saveQuietly();
        }

        $response = $next($request);

        $this->persistSessionState($request, $token);

        return $response;
    }

    /**
     * Same access rules as the PWA login + gate middleware. Null = allowed.
     */
    public static function denyReason(string $app, $owner): ?string
    {
        if ($app === MobileAppToken::APP_CUSTOMER) {
            if (! $owner instanceof Contact) {
                return 'Account not found.';
            }
            if ((int) ($owner->allow_customer_app ?? 0) !== 1 || empty($owner->password)) {
                return 'Customer app access is not enabled for this account.';
            }
            if (! in_array((string) $owner->type, ['customer', 'both'], true)) {
                return 'Only customers can use this app.';
            }

            return null;
        }

        if (! $owner instanceof User) {
            return 'Account not found.';
        }

        $business = $owner->business_id ? Business::find($owner->business_id) : null;
        if (! $business || empty($business->is_active)) {
            return __('lang_v1.business_inactive');
        }

        if ($app === MobileAppToken::APP_SALE) {
            if ($owner->status != 'active' || ! $owner->allow_login || $owner->user_type !== 'user') {
                return __('lang_v1.login_not_allowed');
            }

            return SaleRepGate::allows($owner) ? null : SaleRepGate::denyMessage($owner);
        }

        // delivery (CheckUserLogin + EnsureDeliveryAppAccess)
        if ($owner->user_type !== 'user' || (int) $owner->allow_login !== 1) {
            return __('lang_v1.login_not_allowed');
        }

        return EnsureDeliveryAppAccess::allows($owner) ? null : EnsureDeliveryAppAccess::denyMessage($owner);
    }

    public static function deny(string $message, int $status)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => null,
        ], $status);
    }

    protected function startArraySession(Request $request, MobileAppToken $token, string $app, $owner): void
    {
        $manager = app('session');
        $manager->setDefaultDriver('array');
        $store = $manager->driver('array');
        $store->start();
        app()->instance('session.store', $store);
        $request->setLaravelSession($store);

        foreach (self::PERSISTED_KEYS as $key) {
            $value = $token->getMetaValue($key);
            if ($value !== null) {
                $store->put($key, $value);
            }
        }

        $business = Business::find((int) ($owner->business_id ?? 0));
        if (! $business) {
            return;
        }

        if ($owner instanceof User) {
            // Mirrors App\Http\Middleware\SetSessionData
            $store->put('user', array_merge((array) $store->get('user', []), [
                'id' => $owner->id,
                'surname' => $owner->surname,
                'first_name' => $owner->first_name,
                'last_name' => $owner->last_name,
                'email' => $owner->email,
                'business_id' => $owner->business_id,
                'language' => $owner->language,
            ]));
            $store->put('business', $business);
            $currency = $business->currency;
            if ($currency) {
                $store->put('currency', [
                    'id' => $currency->id,
                    'code' => $currency->code,
                    'symbol' => $currency->symbol,
                    'thousand_separator' => $currency->thousand_separator,
                    'decimal_separator' => $currency->decimal_separator,
                ]);
            }
            $store->put('financial_year', (new BusinessUtil)->getCurrentFinancialYear($business->id));

            if (! empty($owner->language)) {
                App::setLocale($owner->language);
            }
        }

        // Business timezone (Timezone middleware)
        $timezone = ! empty($business->time_zone) ? $business->time_zone : config('app.timezone');
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }

    protected function persistSessionState(Request $request, MobileAppToken $token): void
    {
        if (! $request->hasSession() || ! $token->exists) {
            return;
        }

        $meta = $token->meta ?? [];
        $changed = false;
        foreach (self::PERSISTED_KEYS as $key) {
            $value = $request->session()->get($key);
            if ($value !== null && data_get($meta, $key) != $value) {
                data_set($meta, $key, $value);
                $changed = true;
            }
        }

        if ($changed) {
            $token->forceFill(['meta' => $meta])->saveQuietly();
        }
    }
}

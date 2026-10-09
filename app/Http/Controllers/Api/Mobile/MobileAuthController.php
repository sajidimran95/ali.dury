<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Http\Controllers\Api\Mobile\Concerns\MobileApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Middleware\MobileApiAuth;
use App\MobileAppToken;
use App\User;
use App\Utils\BusinessUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Login / logout / profile for the three native apps.
 * Same credential rules as the PWA login screens:
 *   Sales     → username + password (SaleAuthController + SaleRepGate)
 *   Delivery  → username + password (DeliveryAuthController + EnsureDeliveryAppAccess)
 *   Customer  → email or mobile + password (CustomerAuthController)
 */
class MobileAuthController extends Controller
{
    use MobileApiResponses;

    public function __construct(protected BusinessUtil $businessUtil) {}

    /**
     * Public: lets the app verify a base URL before showing the login screen.
     */
    public function ping()
    {
        return $this->ok([
            'app_name' => config('app.name'),
            'api' => 'japspos-mobile',
            'api_version' => config('mobile_api.version'),
            'base_url' => url('/'),
            'server_time' => now()->toIso8601String(),
            'apps' => ['sale', 'delivery', 'customer'],
        ], 'Server reachable.');
    }

    // ------------------------------------------------------------------ Sales

    public function saleLogin(Request $request)
    {
        return $this->staffLogin($request, MobileAppToken::APP_SALE);
    }

    // --------------------------------------------------------------- Delivery

    public function deliveryLogin(Request $request)
    {
        return $this->staffLogin($request, MobileAppToken::APP_DELIVERY);
    }

    // --------------------------------------------------------------- Customer

    public function customerLogin(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string|max:191',
            'password' => 'required|string|max:191',
            'device_name' => 'nullable|string|max:191',
        ]);

        $throttleKey = 'mobile-login|customer|'.mb_strtolower(trim($data['login'])).'|'.$request->ip();
        $this->ensureNotThrottled($throttleKey);

        $login = trim($data['login']);
        $loginDigits = preg_replace('/\D+/', '', $login);

        // Same lookup as CustomerAuthController::login
        $contact = Contact::whereIn('type', ['customer', 'both'])
            ->where('allow_customer_app', 1)
            ->whereNotNull('password')
            ->where('password', '!=', '')
            ->where(function ($q) use ($login, $loginDigits) {
                $q->where('email', $login);
                if ($loginDigits !== '') {
                    $q->orWhere('mobile', $login)
                        ->orWhere('mobile', $loginDigits)
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(mobile,''),'(',''),')',''),'-',''),' ',''),'+','') = ?", [$loginDigits]);
                }
            })
            ->first();

        if (! $contact || ! Hash::check($data['password'], $contact->password)) {
            RateLimiter::hit($throttleKey, 60);

            return $this->fail('Invalid email/mobile or password.', 401);
        }
        RateLimiter::clear($throttleKey);

        $denied = MobileApiAuth::denyReason(MobileAppToken::APP_CUSTOMER, $contact);
        if ($denied !== null) {
            return $this->fail($denied, 403);
        }

        [, $plain] = MobileAppToken::issue(
            MobileAppToken::APP_CUSTOMER,
            $contact,
            (int) $contact->business_id,
            $data['device_name'] ?? null
        );

        return $this->ok([
            'token' => $plain,
            'token_type' => 'Bearer',
            'customer' => $this->customerPayload($contact),
            'business' => $this->businessPayload((int) $contact->business_id),
        ], 'Logged in.');
    }

    // ----------------------------------------------------------------- Shared

    public function me(Request $request)
    {
        $token = $request->attributes->get('mobile_token');
        $app = $token->app;

        if ($app === MobileAppToken::APP_CUSTOMER) {
            $contact = Auth::guard('customer')->user();

            return $this->ok([
                'customer' => $this->customerPayload($contact),
                'business' => $this->businessPayload((int) $contact->business_id),
            ]);
        }

        $user = Auth::guard($app)->user();

        return $this->ok([
            'user' => $this->userPayload($user, $app),
            'business' => $this->businessPayload((int) $user->business_id),
            'current_location' => $this->currentLocationPayload($request, (int) $user->business_id),
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->attributes->get('mobile_token');
        if ($token->app !== MobileAppToken::APP_CUSTOMER) {
            $user = Auth::guard($token->app)->user();
            if ($user) {
                $this->businessUtil->activityLog($user, 'logout');
            }
        }
        $token->delete();

        return $this->ok(null, 'Logged out.');
    }

    /**
     * Revoke every device token of this account for this app.
     */
    public function logoutAll(Request $request)
    {
        $token = $request->attributes->get('mobile_token');
        $count = MobileAppToken::where('app', $token->app)
            ->where('tokenable_type', $token->tokenable_type)
            ->where('tokenable_id', $token->tokenable_id)
            ->delete();

        return $this->ok(['revoked' => $count], 'Logged out from all devices.');
    }

    // --------------------------------------------------------------- Helpers

    protected function staffLogin(Request $request, string $app)
    {
        $data = $request->validate([
            'username' => 'required|string|max:191',
            'password' => 'required|string|max:191',
            'device_name' => 'nullable|string|max:191',
        ]);

        $throttleKey = 'mobile-login|'.$app.'|'.mb_strtolower(trim($data['username'])).'|'.$request->ip();
        $this->ensureNotThrottled($throttleKey);

        $user = User::where('username', trim($data['username']))->first();
        if (! $user || ! Hash::check($data['password'], (string) $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return $this->fail(trans('auth.failed'), 401);
        }
        RateLimiter::clear($throttleKey);

        $denied = MobileApiAuth::denyReason($app, $user);
        if ($denied !== null) {
            return $this->fail($denied, 403);
        }

        $this->businessUtil->activityLog($user, 'login', null, [], false, $user->business_id);

        [$token, $plain] = MobileAppToken::issue($app, $user, (int) $user->business_id, $data['device_name'] ?? null);

        // Default location (same rule as the Sales PWA: first permitted location)
        if ($app === MobileAppToken::APP_SALE) {
            Auth::shouldUse('sale');
            Auth::guard('sale')->setUser($user);
            $locations = BusinessLocation::forDropdown($user->business_id, false);
            $first = count($locations) ? array_key_first($locations->toArray()) : null;
            if ($first) {
                $token->forceFill(['meta' => ['user' => ['default_location_id' => (int) $first]]])->saveQuietly();
            }
        }

        return $this->ok([
            'token' => $plain,
            'token_type' => 'Bearer',
            'expires_at' => optional($token->expires_at)->toIso8601String(),
            'user' => $this->userPayload($user, $app),
            'business' => $this->businessPayload((int) $user->business_id),
        ], 'Logged in.');
    }

    protected function ensureNotThrottled(string $key): void
    {
        $max = max(1, (int) config('mobile_api.login_throttle_per_minute', 10));
        if (RateLimiter::tooManyAttempts($key, $max)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'login' => [trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)])],
            ])->status(429);
        }
    }

    protected function userPayload(User $user, string $app): array
    {
        $role = '';
        try {
            $raw = (string) ($user->getRoleNames()->first() ?? '');
            $role = trim(explode('#', $raw)[0]);
        } catch (\Throwable $e) {
        }

        $payload = [
            'id' => $user->id,
            'username' => $user->username,
            'surname' => $user->surname,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => trim(implode(' ', array_filter([$user->surname, $user->first_name, $user->last_name]))),
            'email' => $user->email,
            'contact_number' => $user->contact_number,
            'language' => $user->language,
            'role' => $role,
        ];

        if ($app === MobileAppToken::APP_SALE) {
            if (strcasecmp($payload['role'], 'Sales Representative') === 0 || $payload['role'] === '') {
                $payload['role'] = 'Sales Rep';
            }
            $payload['permissions'] = [
                'edit_order_price' => $user->can('edit_product_price_from_sales_app'),
                'order_update' => $user->can('so.update') || $user->can('so.update_own'),
                'order_delete' => $user->can('so.delete') || $user->can('so.delete_own'),
                'customer_list' => $user->can('customer.view') || $user->can('customer.view_own'),
                'customer_create' => $user->can('customer.create'),
                'expense_list' => $user->can('view_own_expense') || $user->can('all_expense.access'),
                'expense_add' => $user->can('expense.add'),
                'stock_transfer_create' => $user->can('stock_transfer.create'),
                'attendance' => true,
            ];
        } else {
            $payload['permissions'] = [
                'driver_view' => $user->can('delivery.driver.view'),
                'driver_edit' => $user->can('delivery.driver.edit'),
                'delivery_manage' => $user->can('delivery.manage'),
            ];
        }

        return $payload;
    }

    protected function customerPayload(Contact $c): array
    {
        $name = trim((string) ($c->supplier_business_name ?: $c->name ?: 'C'));
        $parts = preg_split('/\s+/', $name) ?: [];
        $initials = strtoupper(substr($parts[0] ?? 'C', 0, 1)).(isset($parts[1]) ? strtoupper(substr($parts[1], 0, 1)) : '');

        return [
            'id' => $c->id,
            'contact_id' => $c->contact_id,
            'name' => $c->name,
            'first_name' => $c->first_name,
            'last_name' => $c->last_name,
            'business_name' => $c->supplier_business_name,
            'display_name' => $name,
            'initials' => $initials,
            'email' => $c->email,
            'mobile' => $c->mobile,
            'type' => $c->type,
            'customer_group_id' => $c->customer_group_id,
            'credit_limit' => $c->credit_limit !== null ? (float) $c->credit_limit : null,
            'address_line_1' => $c->address_line_1,
            'address_line_2' => $c->address_line_2,
            'city' => $c->city,
            'state' => $c->state,
            'zip_code' => $c->zip_code,
            'country' => $c->country,
            'shipping_address' => $c->shipping_address,
        ];
    }

    public static function businessPayloadFor(int $businessId): ?array
    {
        $business = Business::with('currency')->find($businessId);
        if (! $business) {
            return null;
        }

        $logo = null;
        if (! empty($business->logo) && file_exists(public_path('uploads/business_logos/'.$business->logo))) {
            $logo = asset('uploads/business_logos/'.$business->logo);
        }

        return [
            'id' => $business->id,
            'name' => $business->name,
            'logo' => $logo,
            'time_zone' => $business->time_zone,
            'date_format' => $business->date_format,
            'time_format' => $business->time_format,
            'currency_precision' => (int) ($business->currency_precision ?? 2),
            'quantity_precision' => (int) ($business->quantity_precision ?? 2),
            'currency' => $business->currency ? [
                'id' => $business->currency->id,
                'code' => $business->currency->code,
                'symbol' => $business->currency->symbol,
                'thousand_separator' => $business->currency->thousand_separator,
                'decimal_separator' => $business->currency->decimal_separator,
                'symbol_placement' => $business->currency_symbol_placement ?? 'before',
            ] : null,
        ];
    }

    protected function businessPayload(int $businessId): ?array
    {
        return self::businessPayloadFor($businessId);
    }

    protected function currentLocationPayload(Request $request, int $businessId): ?array
    {
        $id = $request->session()->get('user.default_location_id');
        if (! $id) {
            return null;
        }
        $loc = BusinessLocation::where('business_id', $businessId)->find($id);

        return $loc ? ['id' => $loc->id, 'name' => $loc->name, 'location_id' => $loc->location_id] : null;
    }
}

<?php

namespace App\Http\Controllers\Sale;

use App\Business;
use App\Http\Controllers\Controller;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Sales representative login (staff users).
 * Uses dedicated "sale" guard so Admin / Customer stay logged in on the same device.
 */
class SaleAuthController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/sale';

    protected $businessUtil;

    protected $moduleUtil;

    public function __construct(BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->middleware('guest:sale')->except('logout');
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Sales App auth guard (not Admin "web").
     */
    protected function guard()
    {
        return Auth::guard('sale');
    }

    public function showLoginForm()
    {
        return view('sale.auth.login');
    }

    public function username()
    {
        return 'username';
    }

    protected function authenticated(Request $request, $user)
    {
        $this->businessUtil->activityLog($user, 'login', null, [], false, $user->business_id);

        $business = $user->business_id ? Business::find($user->business_id) : null;

        if (! $business || empty($business->is_active)) {
            $this->guard()->logout();

            return redirect()->route('sale.login')
                ->with('status', ['success' => 0, 'msg' => __('lang_v1.business_inactive')]);
        }

        if ($user->status != 'active' || ! $user->allow_login || $user->user_type !== 'user') {
            $this->guard()->logout();

            return redirect()->route('sale.login')
                ->with('status', ['success' => 0, 'msg' => __('lang_v1.login_not_allowed')]);
        }

        // Sales App: sales reps only — block Admin and users without sell access
        if (! SaleRepGate::allows($user)) {
            $this->guard()->logout();
            // Do NOT invalidate the whole session — Admin may be logged in on another cookie
            $request->session()->forget('url.intended');

            return redirect()->route('sale.login')
                ->with('status', ['success' => 0, 'msg' => SaleRepGate::denyMessage($user)]);
        }

        // Same business timezone as admin system (Business Settings → Time zone)
        $this->applyBusinessTimezone($request, $user, $business);

        return redirect()->intended(route('sale.home'));
    }

    /**
     * Seed session + PHP/Laravel timezone from business (matches SetSessionData + Timezone middleware).
     */
    protected function applyBusinessTimezone(Request $request, $user, Business $business): void
    {
        $timezone = ! empty($business->time_zone) ? $business->time_zone : config('app.timezone');

        if (! $request->session()->has('user')) {
            $currency = $business->currency;
            $request->session()->put('user', [
                'id' => $user->id,
                'surname' => $user->surname,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'business_id' => $user->business_id,
                'language' => $user->language,
            ]);
            $request->session()->put('business', $business);
            if ($currency) {
                $request->session()->put('currency', [
                    'id' => $currency->id,
                    'code' => $currency->code,
                    'symbol' => $currency->symbol,
                    'thousand_separator' => $currency->thousand_separator,
                    'decimal_separator' => $currency->decimal_separator,
                ]);
            }
            $request->session()->put('financial_year', $this->businessUtil->getCurrentFinancialYear($business->id));
        } else {
            $request->session()->put('business', $business);
        }

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }

    public function logout(Request $request)
    {
        if ($this->guard()->check()) {
            $this->businessUtil->activityLog($this->guard()->user(), 'logout');
        }

        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('sale.login');
    }
}

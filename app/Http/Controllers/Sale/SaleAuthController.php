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
 * Sales representative login (staff users). Separate from ecommerce customers & main /login UI.
 */
class SaleAuthController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/sale';

    protected $businessUtil;

    protected $moduleUtil;

    public function __construct(BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->middleware('guest')->except('logout');
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
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
            Auth::logout();

            return redirect()->route('sale.login')
                ->with('status', ['success' => 0, 'msg' => __('lang_v1.business_inactive')]);
        }

        if ($user->status != 'active' || ! $user->allow_login || $user->user_type !== 'user') {
            Auth::logout();

            return redirect()->route('sale.login')
                ->with('status', ['success' => 0, 'msg' => __('lang_v1.login_not_allowed')]);
        }

        $canSell = $user->can('sell.create')
            || $user->can('direct_sell.access')
            || $user->can('direct_sell.view')
            || $user->can('view_own_sell_only');

        if (! $canSell) {
            Auth::logout();

            return redirect()->route('sale.login')
                ->with('status', ['success' => 0, 'msg' => 'Your account does not have sales access.']);
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
        if (auth()->check()) {
            $this->businessUtil->activityLog(auth()->user(), 'logout');
        }

        $request->session()->flush();
        Auth::logout();

        return redirect()->route('sale.login');
    }
}

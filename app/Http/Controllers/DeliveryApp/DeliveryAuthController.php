<?php

namespace App\Http\Controllers\DeliveryApp;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureDeliveryAppAccess;
use App\User;
use App\Utils\BusinessUtil;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryAuthController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/delivery';

    protected $businessUtil;

    public function __construct(BusinessUtil $businessUtil)
    {
        $this->middleware('guest:delivery')->except('logout');
        $this->businessUtil = $businessUtil;
    }

    protected function guard()
    {
        return Auth::guard('delivery');
    }

    public function showLoginForm()
    {
        if (Auth::guard('delivery')->check() && EnsureDeliveryAppAccess::allows(Auth::guard('delivery')->user())) {
            return redirect()->route('delivery.app.home');
        }

        return view('delivery_app.login');
    }

    public function username()
    {
        return 'username';
    }

    protected function authenticated(Request $request, $user)
    {
        if (! EnsureDeliveryAppAccess::allows($user)) {
            $this->guard()->logout();
            $request->session()->forget('url.intended');

            return redirect()->route('delivery.app.login')
                ->with('status', ['success' => 0, 'msg' => EnsureDeliveryAppAccess::denyMessage($user)]);
        }

        if ($user->business_id) {
            $request->session()->put('user.business_id', $user->business_id);
            $request->session()->put('user.id', $user->id);
        }

        return redirect()->intended(route('delivery.app.home'));
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->regenerateToken();

        return redirect()->route('delivery.app.login');
    }
}

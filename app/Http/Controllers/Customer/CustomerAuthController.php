<?php

namespace App\Http\Controllers\Customer;

use App\Contact;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.home');
        }

        return view('customer.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string|max:191',
            'password' => 'required|string|max:191',
        ]);

        $login = trim($data['login']);
        // Mobile: digits only (ignore (), -, spaces, etc.)
        $loginDigits = preg_replace('/\D+/', '', $login);

        $contact = Contact::whereIn('type', ['customer', 'both'])
            ->where('allow_customer_app', 1)
            ->whereNotNull('password')
            ->where('password', '!=', '')
            ->where(function ($q) use ($login, $loginDigits) {
                $q->where('email', $login);
                if ($loginDigits !== '') {
                    // Match stored mobile as-is or as digits-only
                    $q->orWhere('mobile', $login)
                        ->orWhere('mobile', $loginDigits)
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(mobile,''),'(',''),')',''),'-',''),' ',''),'+','') = ?", [$loginDigits]);
                }
            })
            ->first();

        if (! $contact || ! Hash::check($data['password'], $contact->password)) {
            return back()
                ->withInput($request->only('login'))
                ->with('error', 'Invalid email/mobile or password.');
        }

        Auth::guard('customer')->login($contact, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('customer.home'));
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}

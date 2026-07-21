<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Contact;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('ecommerce::front.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $business_id = $request->attributes->get('ecommerce_business_id');
        $contact = Contact::where('business_id', $business_id)
            ->onlyCustomers()
            ->where(function ($q) use ($data) {
                $q->where('email', $data['login'])->orWhere('mobile', $data['login']);
            })
            ->first();

        if (! $contact || empty($contact->password) || ! Hash::check($data['password'], $contact->password)) {
            return back()->withInput()->with('error', __('ecommerce::lang.invalid_credentials'));
        }

        Auth::guard('customer')->login($contact, $request->boolean('remember'));

        return redirect()->route('ecommerce.account')->with('success', __('ecommerce::lang.welcome_back'));
    }

    public function showRegister()
    {
        return view('ecommerce::front.auth.register');
    }

    public function register(Request $request)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'mobile' => 'required|string|max:30',
            'email' => 'required|email|max:191',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $exists = Contact::where('business_id', $business_id)
            ->where(function ($q) use ($data) {
                $q->where('email', $data['email'])->orWhere('mobile', $data['mobile']);
            })
            ->first();

        if ($exists && ! empty($exists->password)) {
            return back()->withInput()->with('error', __('ecommerce::lang.account_exists'));
        }

        if ($exists) {
            $exists->password = Hash::make($data['password']);
            $exists->ecom_first_name = $data['first_name'];
            $exists->ecom_last_name = $data['last_name'];
            $exists->name = trim($data['first_name'].' '.$data['last_name']);
            $exists->email = $data['email'];
            $exists->mobile = $data['mobile'];
            $exists->save();
            $contact = $exists;
        } else {
            $contact = Contact::create([
                'business_id' => $business_id,
                'type' => 'customer',
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'ecom_first_name' => $data['first_name'],
                'ecom_last_name' => $data['last_name'],
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'password' => Hash::make($data['password']),
                'created_by' => 1,
                'contact_status' => 'active',
            ]);
        }

        Auth::guard('customer')->login($contact);

        return redirect()->route('ecommerce.account')->with('success', __('ecommerce::lang.account_created'));
    }

    public function logout()
    {
        Auth::guard('customer')->logout();

        return redirect()->to('/');
    }
}

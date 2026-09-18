<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\BusinessLocation;
use App\Http\Controllers\Controller;
use App\Product;
use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\EcommerceSetting;

class SettingsController extends Controller
{
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $settings = EcommerceSetting::forBusiness($business_id);
        $locations = BusinessLocation::forDropdown($business_id);

        return view('ecommerce::admin.settings.index', compact('settings', 'locations'));
    }

    public function update(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $settings = EcommerceSetting::forBusiness($business_id);

        $settings->is_enabled = $request->boolean('is_enabled');
        $settings->store_name = $request->input('store_name');
        $settings->location_id = $request->input('location_id') ?: null;
        $settings->enable_cod = $request->boolean('enable_cod');
        $settings->enable_stripe = $request->boolean('enable_stripe');
        $settings->enable_paypal = $request->boolean('enable_paypal');
        $settings->stripe_key = $request->input('stripe_key');
        $settings->stripe_secret = $request->input('stripe_secret');
        $settings->paypal_client_id = $request->input('paypal_client_id');
        $settings->paypal_secret = $request->input('paypal_secret');
        $settings->default_country = $request->input('default_country', 'United States');
        $settings->seo_title = $request->input('seo_title');
        $settings->seo_description = $request->input('seo_description');
        $settings->contact_email = $request->input('contact_email');
        $settings->contact_phone = $request->input('contact_phone');
        $extra = is_array($settings->extra) ? $settings->extra : [];
        $extra['about_title'] = $request->input('about_title');
        $extra['about_lede'] = $request->input('about_lede');
        $extra['about_body'] = $request->input('about_body');
        $extra['store_hours'] = $request->input('store_hours');
        $settings->extra = $extra;

        if ($request->hasFile('store_logo')) {
            $request->validate([
                'store_logo' => 'image|mimes:jpg,jpeg,png,webp,svg,gif|max:4096',
            ]);
            $dir = public_path('uploads/ecommerce');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $file = $request->file('store_logo');
            $filename = 'store_logo_'.time().'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $settings->store_logo = 'ecommerce/'.$filename;
        }

        $settings->save();

        // Ensure a usable flat shipping rule exists when store is enabled
        if ($settings->is_enabled) {
            $has_shipping = \Modules\Ecommerce\Entities\EcomShippingRule::where('business_id', $business_id)->exists();
            if (! $has_shipping) {
                \Modules\Ecommerce\Entities\EcomShippingRule::create([
                    'business_id' => $business_id,
                    'name' => 'Standard Shipping',
                    'type' => 'flat',
                    'amount' => 5.99,
                    'is_active' => 1,
                    'sort_order' => 1,
                ]);
            }
        }

        return redirect()->back()->with('status', [
            'success' => 1,
            'msg' => __('ecommerce::lang.settings_updated'),
        ]);
    }
}

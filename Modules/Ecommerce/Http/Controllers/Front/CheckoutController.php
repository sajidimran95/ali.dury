<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Contact;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Ecommerce\Entities\EcomCustomerAddress;
use Modules\Ecommerce\Entities\EcomOrderMeta;
use Modules\Ecommerce\Services\CartService;
use Modules\Ecommerce\Services\CheckoutService;
use Modules\Ecommerce\Services\CouponService;
use Modules\Ecommerce\Services\ShippingCalculator;
use Modules\Ecommerce\Services\TaxCalculator;

class CheckoutController extends Controller
{
    public function index(CartService $cartService, ShippingCalculator $shipping, TaxCalculator $tax, CouponService $coupons)
    {
        if (! auth('customer')->check()) {
            return redirect()->guest(route('ecommerce.login'))
                ->with('error', 'Sign in with your wholesale account to checkout.');
        }

        $customer = auth('customer')->user();
        if (! \Modules\Ecommerce\Support\WholesaleAccount::isApproved($customer)) {
            return redirect()->route('ecommerce.account.licenses')
                ->with('error', 'Your wholesale account is pending approval. Checkout unlocks after an admin reviews your licenses.');
        }

        $business_id = request()->attributes->get('ecommerce_business_id');
        $settings = request()->attributes->get('ecommerce_settings');
        $cart = $cartService->getOrCreateCart($business_id);
        if ($cart->items->isEmpty()) {
            return redirect()->route('ecommerce.cart')->with('error', __('ecommerce::lang.cart_empty'));
        }

        $sub = $cartService->totals($cart);
        $discount = 0;
        if ($cart->coupon_code) {
            try {
                $coupon = $coupons->findValid($business_id, $cart->coupon_code, $sub['subtotal']);
                $discount = $coupons->discountAmount($coupon, $sub['subtotal']);
            } catch (\Throwable $e) {
                $cart->coupon_code = null;
                $cart->save();
            }
        }
        $ship = $shipping->calculate($business_id, max(0, $sub['subtotal'] - $discount));
        $tax_amt = $tax->calculate($business_id, max(0, $sub['subtotal'] - $discount));
        $totals = $cartService->totals($cart, $ship, $tax_amt, $discount);

        $addresses = [];
        if (auth('customer')->check()) {
            $addresses = EcomCustomerAddress::where('contact_id', auth('customer')->id())->get();
        }

        $us_states = $this->usStates();

        return view('ecommerce::front.checkout.index', compact('cart', 'totals', 'settings', 'addresses', 'us_states', 'discount'));
    }

    public function place(Request $request, CheckoutService $checkout)
    {
        $customer = auth('customer')->user();
        if ($customer && ! \Modules\Ecommerce\Support\WholesaleAccount::isApproved($customer)) {
            return redirect()->route('ecommerce.account.licenses')
                ->with('error', 'Checkout is locked until your wholesale account is approved.');
        }

        $business_id = $request->attributes->get('ecommerce_business_id');
        $data = $request->validate([
            'email' => 'required|email',
            'phone' => 'nullable|string|max:30',
            'payment_method' => 'required|in:cod,stripe,paypal',
            'note' => 'nullable|string|max:500',
            'shipping.first_name' => 'required|string|max:100',
            'shipping.last_name' => 'required|string|max:100',
            'shipping.address_line_1' => 'required|string|max:255',
            'shipping.address_line_2' => 'nullable|string|max:255',
            'shipping.city' => 'required|string|max:100',
            'shipping.state' => 'required|string|max:100',
            'shipping.zip_code' => 'required|string|max:20',
            'shipping.country' => 'nullable|string|max:100',
            'billing_same' => 'nullable',
            'coupon_code' => 'nullable|string',
        ]);

        $contact = auth('customer')->user();
        if (! $contact) {
            return redirect()->route('ecommerce.login')
                ->with('error', 'Please sign in with an approved wholesale account to checkout.');
        }

        $data['shipping']['country'] = $data['shipping']['country'] ?? 'United States';
        $data['billing_same'] = $request->boolean('billing_same', true);

        try {
            $result = $checkout->placeOrder($business_id, $contact->id, $data);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('ecommerce.thanks', ['token' => $result['tracking_token']])
            ->with('success', __('ecommerce::lang.order_placed'));
    }

    public function thanks($token)
    {
        $meta = EcomOrderMeta::where('tracking_token', $token)->firstOrFail();
        $order = \App\Transaction::with(['sell_lines', 'sell_lines.product', 'contact'])->findOrFail($meta->transaction_id);

        return view('ecommerce::front.checkout.thanks', compact('meta', 'order'));
    }

    public function track(Request $request)
    {
        $token = $request->get('token');
        $meta = null;
        $order = null;
        if ($token) {
            $meta = EcomOrderMeta::where('tracking_token', $token)->first();
            if ($meta) {
                $order = \App\Transaction::with(['sell_lines.product'])->find($meta->transaction_id);
            }
        }

        return view('ecommerce::front.account.track', compact('meta', 'order', 'token'));
    }

    protected function usStates(): array
    {
        return [
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
            'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia',
            'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
            'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
            'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri',
            'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey',
            'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
            'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
            'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont',
            'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
            'DC' => 'District of Columbia',
        ];
    }
}

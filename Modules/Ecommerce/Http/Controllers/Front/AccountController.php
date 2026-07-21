<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Ecommerce\Entities\EcomCustomerAddress;
use Modules\Ecommerce\Entities\EcomOrderMeta;
use Modules\Ecommerce\Entities\EcomReview;
use Modules\Ecommerce\Entities\EcomWishlist;
use Modules\Ecommerce\Services\CartService;

class AccountController extends Controller
{
    public function index()
    {
        $contact = auth('customer')->user();
        $orders = Transaction::where('contact_id', $contact->id)
            ->where('source', 'ecommerce')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('ecommerce::front.account.index', compact('contact', 'orders'));
    }

    public function orders()
    {
        $orders = Transaction::where('contact_id', auth('customer')->id())
            ->where('source', 'ecommerce')
            ->orderByDesc('id')
            ->paginate(20);
        $metas = EcomOrderMeta::whereIn('transaction_id', $orders->pluck('id'))->get()->keyBy('transaction_id');

        return view('ecommerce::front.account.orders', compact('orders', 'metas'));
    }

    public function profile(Request $request)
    {
        $contact = auth('customer')->user();
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|email',
                'mobile' => 'required|string|max:30',
            ]);
            $contact->ecom_first_name = $data['first_name'];
            $contact->ecom_last_name = $data['last_name'];
            $contact->name = trim($data['first_name'].' '.$data['last_name']);
            $contact->email = $data['email'];
            $contact->mobile = $data['mobile'];
            $contact->save();

            return back()->with('success', __('ecommerce::lang.profile_updated'));
        }

        return view('ecommerce::front.account.profile', compact('contact'));
    }

    public function password(Request $request)
    {
        $contact = auth('customer')->user();
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'current_password' => 'required',
                'password' => 'required|min:6|confirmed',
            ]);
            if (! Hash::check($data['current_password'], $contact->password)) {
                return back()->with('error', __('ecommerce::lang.invalid_credentials'));
            }
            $contact->password = Hash::make($data['password']);
            $contact->save();

            return back()->with('success', __('ecommerce::lang.password_updated'));
        }

        return view('ecommerce::front.account.password');
    }

    public function addresses(Request $request)
    {
        $contact = auth('customer')->user();
        $business_id = request()->attributes->get('ecommerce_business_id');
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'company' => 'nullable|string',
                'address_line_1' => 'required|string',
                'address_line_2' => 'nullable|string',
                'city' => 'required|string',
                'state' => 'required|string',
                'zip_code' => 'required|string',
                'country' => 'nullable|string',
                'phone' => 'nullable|string',
            ]);
            $data['business_id'] = $business_id;
            $data['contact_id'] = $contact->id;
            $data['country'] = $data['country'] ?? 'United States';
            $data['is_default'] = $request->boolean('is_default');
            if ($data['is_default']) {
                EcomCustomerAddress::where('contact_id', $contact->id)->update(['is_default' => 0]);
            }
            EcomCustomerAddress::create($data);

            return back()->with('success', __('ecommerce::lang.saved'));
        }

        $addresses = EcomCustomerAddress::where('contact_id', $contact->id)->get();

        return view('ecommerce::front.account.addresses', compact('addresses'));
    }

    public function wishlist(CartService $cartService)
    {
        $items = EcomWishlist::where('contact_id', auth('customer')->id())->with(['product.variations'])->get();
        $cart_totals = $cartService->totals($cartService->getOrCreateCart(request()->attributes->get('ecommerce_business_id')));

        return view('ecommerce::front.account.wishlist', compact('items', 'cart_totals'));
    }

    public function toggleWishlist(Request $request)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $product_id = (int) $request->input('product_id');
        $variation_id = $request->input('variation_id');
        $existing = EcomWishlist::where('contact_id', auth('customer')->id())
            ->where('product_id', $product_id)
            ->when($variation_id, fn ($q) => $q->where('variation_id', $variation_id))
            ->first();
        if ($existing) {
            $existing->delete();
        } else {
            EcomWishlist::create([
                'business_id' => $business_id,
                'contact_id' => auth('customer')->id(),
                'product_id' => $product_id,
                'variation_id' => $variation_id,
            ]);
        }

        return back()->with('success', __('ecommerce::lang.wishlist_updated'));
    }

    public function review(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:2000',
        ]);
        EcomReview::create([
            'business_id' => $request->attributes->get('ecommerce_business_id'),
            'product_id' => $data['product_id'],
            'contact_id' => auth('customer')->id(),
            'author_name' => auth('customer')->user()->name,
            'rating' => $data['rating'],
            'review' => $data['review'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', __('ecommerce::lang.review_submitted'));
    }
}

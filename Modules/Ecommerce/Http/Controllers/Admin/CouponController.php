<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\EcomCoupon;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = EcomCoupon::where('business_id', session('user.business_id'))->orderByDesc('id')->get();

        return view('ecommerce::admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ]);
        $data['business_id'] = session('user.business_id');
        $data['is_active'] = $request->boolean('is_active', true);
        EcomCoupon::create($data);

        return back()->with('status', ['success' => 1, 'msg' => __('ecommerce::lang.saved')]);
    }

    public function destroy($id)
    {
        EcomCoupon::where('business_id', session('user.business_id'))->where('id', $id)->delete();

        return back()->with('status', ['success' => 1, 'msg' => __('ecommerce::lang.deleted')]);
    }
}

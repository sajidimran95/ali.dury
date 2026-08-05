<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\EcomCoupon;

class CouponController extends Controller
{
    public function index()
    {
        $business_id = (int) session('user.business_id');
        $coupons = EcomCoupon::where('business_id', $business_id)
            ->withCount('usages')
            ->orderByDesc('used_count')
            ->orderByDesc('id')
            ->get()
            ->map(function (EcomCoupon $coupon) {
                $fromColumn = (int) ($coupon->used_count ?? 0);
                $fromUsages = (int) ($coupon->usages_count ?? 0);
                $coupon->times_applied = max($fromColumn, $fromUsages);

                return $coupon;
            });
        $coupon_stats = $this->getEcommerceCouponListStats($business_id, $coupons);

        return view('ecommerce::admin.coupons.index', compact('coupons', 'coupon_stats'));
    }

    /**
     * KPI stats for ecommerce coupons list cards.
     */
    protected function getEcommerceCouponListStats(int $businessId, $coupons = null): array
    {
        $base = EcomCoupon::where('business_id', $businessId);

        $total = (int) (clone $base)->count();
        $active = (int) (clone $base)->where('is_active', 1)->count();
        $inactive = max(0, $total - $active);

        if ($coupons === null) {
            $coupons = (clone $base)->withCount('usages')->get()->map(function (EcomCoupon $coupon) {
                $coupon->times_applied = max((int) $coupon->used_count, (int) $coupon->usages_count);

                return $coupon;
            });
        }

        $usedTimes = (int) $coupons->sum('times_applied');
        $usedCoupons = (int) $coupons->where('times_applied', '>', 0)->count();
        $neverUsed = max(0, $total - $usedCoupons);
        $top = $coupons->sortByDesc('times_applied')->first();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'used_times' => $usedTimes,
            'used_coupons' => $usedCoupons,
            'never_used' => $neverUsed,
            'top_code' => $top && ($top->times_applied ?? 0) > 0 ? $top->code : null,
            'top_applied' => $top ? (int) ($top->times_applied ?? 0) : 0,
        ];
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

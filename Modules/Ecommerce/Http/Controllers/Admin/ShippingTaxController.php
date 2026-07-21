<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\EcomShippingRule;
use Modules\Ecommerce\Entities\EcomTaxRule;

class ShippingTaxController extends Controller
{
    public function index()
    {
        $business_id = session('user.business_id');
        $shipping_rules = EcomShippingRule::where('business_id', $business_id)->orderBy('sort_order')->get();
        $tax_rules = EcomTaxRule::where('business_id', $business_id)->orderBy('id')->get();

        return view('ecommerce::admin.settings.shipping_tax', compact('shipping_rules', 'tax_rules'));
    }

    public function storeShipping(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:flat,free,weight,state',
            'amount' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'states' => 'nullable|string',
        ]);
        $data['business_id'] = session('user.business_id');
        $data['is_active'] = true;
        if (! empty($data['states'])) {
            $data['states'] = array_map('trim', explode(',', $data['states']));
        } else {
            unset($data['states']);
        }
        EcomShippingRule::create($data);

        return back()->with('status', ['success' => 1, 'msg' => __('ecommerce::lang.saved')]);
    }

    public function storeTax(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'level' => 'required|in:state,county,city',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'rate' => 'required|numeric|min:0',
        ]);
        $data['business_id'] = session('user.business_id');
        $data['is_active'] = true;
        EcomTaxRule::create($data);

        return back()->with('status', ['success' => 1, 'msg' => __('ecommerce::lang.saved')]);
    }
}

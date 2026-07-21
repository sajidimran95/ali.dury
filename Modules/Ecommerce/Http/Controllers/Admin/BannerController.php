<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\EcomBanner;

class BannerController extends Controller
{
    public function index()
    {
        $banners = EcomBanner::where('business_id', session('user.business_id'))->orderBy('sort_order')->get();

        return view('ecommerce::admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:191',
            'subtitle' => 'nullable|string|max:191',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|max:4096',
        ]);
        $data['business_id'] = session('user.business_id');
        $data['is_active'] = $request->boolean('is_active', true);
        if ($request->hasFile('image')) {
            $name = time().'_banner.'.$request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads/img'), $name);
            $data['image'] = $name;
        }
        EcomBanner::create($data);

        return back()->with('status', ['success' => 1, 'msg' => __('ecommerce::lang.saved')]);
    }

    public function destroy($id)
    {
        EcomBanner::where('business_id', session('user.business_id'))->where('id', $id)->delete();

        return back()->with('status', ['success' => 1, 'msg' => __('ecommerce::lang.deleted')]);
    }
}

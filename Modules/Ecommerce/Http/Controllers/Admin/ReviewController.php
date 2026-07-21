<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\EcomReview;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = EcomReview::where('business_id', session('user.business_id'))
            ->with(['product', 'contact'])
            ->orderByDesc('id')
            ->paginate(30);

        return view('ecommerce::admin.reviews.index', compact('reviews'));
    }

    public function updateStatus(Request $request, $id)
    {
        $review = EcomReview::where('business_id', session('user.business_id'))->findOrFail($id);
        $review->status = $request->input('status');
        $review->save();

        return back()->with('status', ['success' => 1, 'msg' => __('ecommerce::lang.status_updated')]);
    }
}

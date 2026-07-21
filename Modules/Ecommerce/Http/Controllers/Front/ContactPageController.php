<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactPageController extends Controller
{
    public function index()
    {
        return view('ecommerce::front.home.contact');
    }

    public function send(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string|max:2000',
        ]);

        return back()->with('success', __('ecommerce::lang.message_sent'));
    }
}

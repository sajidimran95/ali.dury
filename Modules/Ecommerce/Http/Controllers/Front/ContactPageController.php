<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\Ecommerce\Entities\EcomInquiry;

class ContactPageController extends Controller
{
    public function index()
    {
        return view('ecommerce::front.home.contact');
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:191',
            'message' => 'required|string|max:2000',
        ]);

        if (Schema::hasTable('ecom_inquiries')) {
            EcomInquiry::create([
                'business_id' => $request->attributes->get('ecommerce_business_id'),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'subject' => $data['subject'] ?? null,
                'message' => $data['message'],
                'source' => 'contact',
            ]);
        }

        return back()->with('success', __('ecommerce::lang.message_sent'));
    }
}

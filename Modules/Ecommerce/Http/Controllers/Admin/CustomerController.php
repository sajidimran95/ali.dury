<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Contact;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Contact::where('business_id', session('user.business_id'))
            ->onlyCustomers()
            ->whereNotNull('password')
            ->orderByDesc('id')
            ->paginate(30);

        return view('ecommerce::admin.customers.index', compact('customers'));
    }
}

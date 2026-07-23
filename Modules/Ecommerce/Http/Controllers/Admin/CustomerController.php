<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Contact;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\EcomCustomerLicense;
use Modules\Ecommerce\Support\WholesaleAccount;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::where('business_id', session('user.business_id'))
            ->onlyCustomers()
            ->whereNotNull('password')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->where(function ($q) {
                    $q->whereNull('custom_field6')
                        ->orWhere('custom_field6', '')
                        ->orWhere('custom_field6', WholesaleAccount::APPROVED);
                });
            } else {
                $query->where('custom_field6', $request->status);
            }
        }

        $customers = $query->paginate(30);

        return view('ecommerce::admin.customers.index', compact('customers'));
    }

    public function show($id)
    {
        $customer = Contact::where('business_id', session('user.business_id'))
            ->onlyCustomers()
            ->whereNotNull('password')
            ->findOrFail($id);

        $licenses = EcomCustomerLicense::where('contact_id', $customer->id)->orderByDesc('id')->get();
        $approval = WholesaleAccount::status($customer);

        return view('ecommerce::admin.customers.show', compact('customer', 'licenses', 'approval'));
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $customer = Contact::where('business_id', session('user.business_id'))
            ->onlyCustomers()
            ->whereNotNull('password')
            ->findOrFail($id);

        WholesaleAccount::setStatus($customer, $data['status']);

        return back()->with('status', 'Customer approval updated to '.$data['status'].'.');
    }
}

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
        $business_id = (int) session('user.business_id');

        $query = Contact::where('business_id', $business_id)
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
        $customer_stats = $this->getEcommerceCustomerListStats($business_id);

        return view('ecommerce::admin.customers.index', compact('customers', 'customer_stats'));
    }

    /**
     * KPI stats for ecommerce online customers list cards.
     */
    protected function getEcommerceCustomerListStats(int $businessId): array
    {
        $base = Contact::where('business_id', $businessId)
            ->onlyCustomers()
            ->whereNotNull('password');

        $total = (int) (clone $base)->count();
        $pending = (int) (clone $base)->where('custom_field6', WholesaleAccount::PENDING)->count();
        $rejected = (int) (clone $base)->where('custom_field6', WholesaleAccount::REJECTED)->count();
        $approved = (int) (clone $base)->where(function ($q) {
            $q->whereNull('custom_field6')
                ->orWhere('custom_field6', '')
                ->orWhere('custom_field6', WholesaleAccount::APPROVED);
        })->count();
        $thisMonth = (int) (clone $base)
            ->whereBetween('created_at', [
                \Carbon\Carbon::now()->startOfMonth()->toDateTimeString(),
                \Carbon\Carbon::now()->endOfMonth()->toDateTimeString(),
            ])
            ->count();

        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'this_month' => $thisMonth,
        ];
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

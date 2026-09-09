<?php

namespace Modules\Crm\Http\Controllers;

use App\SaleZeroSale;
use App\SaleZeroSaleReason;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ZeroSaleController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->user()->can('crm.access')
            && ! auth()->user()->can('customer.view')
            && ! auth()->user()->can('customer.view_own')
            && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $contact_id = $request->get('contact_id');
        $reason_id = $request->get('reason_id');
        $q = trim((string) $request->get('q', ''));

        $query = SaleZeroSale::where('sale_zero_sales.business_id', $business_id)
            ->with(['contact', 'reason', 'createdBy'])
            ->orderByDesc('sale_zero_sales.created_at');

        if (! empty($contact_id)) {
            $query->where('contact_id', $contact_id);
        }
        if (! empty($reason_id)) {
            $query->where('reason_id', $reason_id);
        }
        if ($q !== '') {
            $query->where(function ($inner) use ($q) {
                $inner->where('comment', 'like', '%'.$q.'%')
                    ->orWhereHas('contact', function ($c) use ($q) {
                        $c->where('name', 'like', '%'.$q.'%')
                            ->orWhere('supplier_business_name', 'like', '%'.$q.'%')
                            ->orWhere('contact_id', 'like', '%'.$q.'%');
                    });
            });
        }

        $rows = $query->paginate(25)->withQueryString();
        $reasons = SaleZeroSaleReason::active()->get(['id', 'name']);

        return view('crm::zero_sales.index', compact('rows', 'reasons', 'contact_id', 'reason_id', 'q'));
    }
}

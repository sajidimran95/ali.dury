<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Contact;
use App\Http\Controllers\Api\Mobile\Concerns\MobileApiResponses;
use App\Http\Controllers\Customer\CustomerPortalController;
use App\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Native Customer app — JSON version of Customer\CustomerPortalController.
 * Every action runs the PWA controller logic and returns its data as JSON.
 */
class CustomerApiController extends CustomerPortalController
{
    use MobileApiResponses;

    public function apiHome(Request $request)
    {
        $d = $this->viewData($this->home());
        $contact = $d['contact'];
        $location = $d['location'] ?? null;

        return $this->ok([
            'customer' => [
                'id' => $contact->id,
                'contact_id' => $contact->contact_id,
                'name' => $contact->name,
                'business_name' => $contact->supplier_business_name,
                'initials' => $d['initials'],
            ],
            'business' => MobileAuthController::businessPayloadFor((int) $contact->business_id),
            'balance_due' => round((float) $d['due'], 2),
            'current_location' => $location ? [
                'id' => $location->id,
                'name' => $location->name,
                'state' => $location->state,
                'city' => $location->city,
                'landmark' => $location->landmark,
            ] : null,
            'locations' => collect($d['locations'])->map(fn ($l) => [
                'id' => $l->id,
                'name' => $l->name,
                'state' => $l->state,
                'city' => $l->city,
                'landmark' => $l->landmark,
            ])->values(),
            'new_arrivals' => collect($d['newArrivals'])->values(),
            'top_products' => collect($d['topProducts'])->values(),
        ]);
    }

    public function apiLocations(Request $request)
    {
        $contact = $this->contact();
        $locations = $this->customerLocations($contact);
        $current = $this->defaultLocationId($contact, $locations);

        return $this->ok([
            'current_location_id' => $current,
            'locations' => $locations->map(fn ($label, $id) => ['id' => (int) $id, 'label' => $label])->values(),
        ]);
    }

    public function apiUpdateLocation(Request $request)
    {
        $response = $this->updateLocation($request);
        $outcome = $this->redirectOutcome($request, $response);
        if (! $outcome['success']) {
            return $this->fail($outcome['message'] ?: 'Invalid location.', 422, $outcome['errors']);
        }

        return $this->ok([
            'current_location_id' => (int) $request->session()->get('customer.default_location_id'),
        ], $outcome['message']);
    }

    public function apiOrders(Request $request)
    {
        $d = $this->viewData($this->orders());

        return $this->ok([
            'orders' => collect($d['orders'])->map(fn ($o) => $this->orderRow($o))->values(),
        ]);
    }

    public function apiShowOrder(Request $request, $id)
    {
        $d = $this->viewData($this->showOrder($id));
        $order = $d['order'];

        return $this->ok([
            'order' => array_merge($this->orderRow($order), [
                'fulfillment' => $d['fulfill'],
                'shipping_method' => $order->shipping_method,
                'shipping_details' => $order->shipping_details,
                'shipping_address' => $order->shipping_address,
                'sale_note' => $order->additional_notes,
                'total_before_tax' => (float) $order->total_before_tax,
                'tax_amount' => (float) $order->tax_amount,
                'discount_type' => $order->discount_type,
                'discount_amount' => (float) $order->discount_amount,
                'shipping_charges' => (float) $order->shipping_charges,
            ]),
            'lines' => collect($d['lines'])->values(),
            'items_total' => round((float) $d['itemsTotal'], 2),
        ]);
    }

    /** Data needed by the "New order" screen. */
    public function apiCreateOrder(Request $request)
    {
        $d = $this->viewData($this->createOrder());

        return $this->ok([
            'default_location_id' => $d['default_location'],
            'locations' => collect($d['locations'])->map(fn ($label, $id) => ['id' => (int) $id, 'label' => $label])->values(),
            'shipping_address' => $d['contact']->shipping_address,
            'fulfillment_options' => ['Pickup', 'Delivery', 'Shipping'],
            'default_fulfillment' => 'Delivery',
        ]);
    }

    public function apiStoreOrder(Request $request)
    {
        $response = $this->storeOrder($request);

        $orderId = null;
        if ($response instanceof RedirectResponse
            && preg_match('#/customer/orders/(\d+)$#', parse_url($response->getTargetUrl(), PHP_URL_PATH) ?: '', $m)) {
            $orderId = (int) $m[1];
        }

        if (! $orderId) {
            $outcome = $this->redirectOutcome($request, $response);

            return $this->fail($outcome['message'] ?: 'Could not place order. Please try again.', 422, $outcome['errors']);
        }

        $order = Transaction::where('business_id', $this->contact()->business_id)
            ->where('contact_id', $this->contact()->id)
            ->find($orderId);

        return $this->ok(
            ['order' => $order ? $this->orderRow($order) : ['id' => $orderId]],
            (string) ($request->session()->get('success') ?: 'Order placed.'),
            201
        );
    }

    public function apiDocuments(Request $request)
    {
        $d = $this->viewData($this->documents($request));

        $rows = collect($d['invoices'])->map(fn ($t) => [
            'id' => $t->id,
            'invoice_no' => $t->invoice_no,
            'transaction_date' => $this->dt($t->transaction_date),
            'date' => $this->dt($t->transaction_date, 'Y-m-d'),
            'final_total' => (float) $t->final_total,
            'tax_amount' => (float) $t->tax_amount,
            'total_paid' => round((float) $t->total_paid, 2),
            'balance_due' => round((float) $t->final_total - (float) $t->total_paid, 2),
            'payment_status' => $t->payment_status,
        ])->values();

        return $this->ok([
            'filters' => [
                'start_date' => $d['start'] !== '' ? $d['start'] : null,
                'end_date' => $d['end'] !== '' ? $d['end'] : null,
                'q' => $d['q'] !== '' ? $d['q'] : null,
            ],
            'summary' => [
                'invoice_count' => (int) $d['taxSummary']['invoice_count'],
                'sales_total' => round((float) $d['taxSummary']['sales_total'], 2),
                'tax_total' => round((float) $d['taxSummary']['tax_total'], 2),
                'paid_total' => round((float) $d['taxSummary']['paid_total'], 2),
            ],
            'invoices' => $rows,
            'groups' => $rows->groupBy('date')->map(fn ($items, $date) => [
                'date' => $date,
                'count' => $items->count(),
                'invoices' => $items->values(),
            ])->values(),
        ]);
    }

    /** Invoice: text/html (default), ?format=json → {html}, ?format=pdf → PDF download. */
    public function apiInvoice(Request $request, $id)
    {
        $response = $this->showInvoice($id);
        if (! $this->isView($response, 'customer.invoice')) {
            $outcome = $this->redirectOutcome($request, $response);

            return $this->fail($outcome['message'] ?: 'Could not open invoice.', 422);
        }
        $d = $this->viewData($response);

        return $this->invoiceResponse($request, (string) $d['html'], (string) $d['title']);
    }

    public function apiProfile(Request $request)
    {
        $d = $this->viewData($this->profile());
        $c = $d['contact'];

        return $this->ok([
            'customer' => [
                'id' => $c->id,
                'contact_id' => $c->contact_id,
                'name' => $c->name,
                'business_name' => $c->supplier_business_name,
                'email' => $c->email,
                'mobile' => $c->mobile,
                'initials' => $d['initials'],
                'address_line_1' => $c->address_line_1,
                'address_line_2' => $c->address_line_2,
                'city' => $c->city,
                'state' => $c->state,
                'zip_code' => $c->zip_code,
                'country' => $c->country,
                'shipping_address' => $c->shipping_address,
                'balance_due' => round((float) $this->util->getContactDue($c->id, (int) $c->business_id), 2),
            ],
            'current_location_id' => $d['current_location_id'],
            'locations' => collect($d['locations'])->map(fn ($label, $id) => ['id' => (int) $id, 'label' => $label])->values(),
        ]);
    }

    public function apiUpdatePassword(Request $request)
    {
        $response = $this->updatePassword($request);
        $outcome = $this->redirectOutcome($request, $response);
        if (! $outcome['success']) {
            return $this->fail($outcome['message'] ?: 'Could not update password.', 422, $outcome['errors']);
        }

        return $this->ok(null, $outcome['message'] ?: 'Password updated successfully.');
    }

    /** Product search / price check (q, category_id, sub_category_id, variation_id, location_id, limit). */
    public function apiProducts(Request $request)
    {
        if (! $request->filled('location_id')) {
            $contact = $this->contact();
            $request->query->set('location_id', (string) $this->defaultLocationId($contact));
        }

        return $this->ok($this->searchProducts($request)->getData(true));
    }

    public function apiCategories(Request $request)
    {
        return $this->ok($this->categoriesTree()->getData(true));
    }

    // ---------------------------------------------------------------- helpers

    protected function orderRow(Transaction $o): array
    {
        return [
            'id' => $o->id,
            'invoice_no' => $o->invoice_no,
            'type' => $o->type,
            'kind' => $o->type === 'sales_order' ? 'order' : 'invoice',
            'status' => $o->status,
            'transaction_date' => $this->dt($o->transaction_date),
            'final_total' => (float) $o->final_total,
            'payment_status' => $o->payment_status,
            'shipping_status' => $o->shipping_status,
            'shipping_method' => $o->shipping_method,
            'location_id' => $o->location_id,
            'source' => $o->source,
        ];
    }
}

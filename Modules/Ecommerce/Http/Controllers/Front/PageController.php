<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $pages = $this->pages();
        abort_unless(isset($pages[$slug]), 404);
        $page = $pages[$slug];

        return view('ecommerce::front.home.page', compact('page'));
    }

    protected function pages(): array
    {
        return [
            'about-us' => [
                'title' => 'About Us',
                'body' => "Greencloud Wholesale is Wisconsin’s premier smoke, vape, hemp and accessories distributor. Since 2018 we have supplied licensed retailers with factory-direct pricing, same-day fulfillment before 2PM CST, and verified products including COAs on hemp SKUs.\n\nWe are based in Wauwatosa, WI 53213 and serve licensed B2B accounts only. Hours: Mon–Fri 9:30AM–5:30PM, Sat 10AM–4PM.",
            ],
            'wholesale-faq' => [
                'title' => 'Wholesale FAQ',
                'body' => "Who can open an account?\nLicensed retailers only. You will need a business certificate, resale certificate, and (if applicable) tobacco license.\n\nWhat is the free shipping threshold?\nStandard shipping is free on orders of $500+.\n\nWhen do orders ship?\nOrders placed before 2PM CST on business days typically ship the same day.\n\nPayment terms?\nCredit card, ACH, or Net-30 for approved accounts.",
            ],
            'shipping-policy' => [
                'title' => 'Shipping Policy',
                'body' => "Orders before 2PM CST (business days) are processed the same day. Free standard shipping on orders $500+. Age-restricted products ship only to licensed wholesale accounts. Tracking is emailed when the shipment leaves our Wauwatosa warehouse.",
            ],
            'return-policy' => [
                'title' => 'Return Policy',
                'body' => "Defective items may be returned within 30 days. Contact us first for an RMA. Opened nicotine, hemp, or hygiene-sensitive products cannot be returned unless defective. Credits are issued to the original payment method after inspection.",
            ],
            'privacy-policy' => [
                'title' => 'Privacy Policy',
                'body' => "We collect business contact details, license information, and order history to operate wholesale accounts. We do not sell personal information. Payment data is processed by our payment providers. Contact info@greencloudwholesale.com for privacy requests.",
            ],
            'terms-of-service' => [
                'title' => 'Terms of Service',
                'body' => "By using this site you confirm you are 21+ and a licensed retailer. Product images are for illustration. Prices and stock change without notice. We may refuse or cancel orders that fail age or license verification. Wisconsin law governs these terms.",
            ],
            'age-compliance' => [
                'title' => 'Age Compliance',
                'body' => "You must be 21 years of age or older to enter this site. This store sells tobacco, nicotine, hemp and age-restricted products for licensed retailers only. We verify licenses before fulfillment. Under-21 access is prohibited.",
            ],
        ];
    }
}

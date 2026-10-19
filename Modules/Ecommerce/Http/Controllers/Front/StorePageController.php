<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Brands;
use App\Category;
use App\Http\Controllers\Controller;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Ecommerce\Entities\EcomInquiry;
use Modules\Ecommerce\Entities\EcomSubscriber;
use Modules\Ecommerce\Services\CartService;
use Modules\Ecommerce\Support\Storefront;

class StorePageController extends Controller
{
    public function page(string $slug)
    {
        $pages = $this->pages();
        abort_unless(isset($pages[$slug]), 404);

        return view('ecommerce::front.pages.show', [
            'page' => $pages[$slug],
            'slug' => $slug,
            'store' => $this->storeName(),
        ]);
    }

    public function categories(Request $request, CartService $cartService)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $categories = Category::where('business_id', $business_id)
            ->where('category_type', 'product')
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->whereIn('name', Storefront::gsTopCategoryNames())
            ->with('sub_categories')
            ->orderBy('name')
            ->get();

        $counts = Product::storefrontQuery((int) $business_id)
            ->select('category_id', DB::raw('count(*) as c'))
            ->groupBy('category_id')
            ->pluck('c', 'category_id');

        return view('ecommerce::front.pages.categories', compact('categories', 'counts'));
    }

    public function brands(Request $request)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $counts = Product::storefrontQuery((int) $business_id)
            ->select('brand_id', DB::raw('count(*) as c'))
            ->groupBy('brand_id')
            ->pluck('c', 'brand_id');
        $brands = Brands::where('business_id', $business_id)
            ->whereIn('id', $counts->keys()->filter()->all() ?: [0])
            ->orderBy('name')
            ->get();
        $brand_covers = is_file(storage_path('app/ecom_brand_covers.json'))
            ? (json_decode(file_get_contents(storage_path('app/ecom_brand_covers.json')), true) ?: [])
            : [];

        return view('ecommerce::front.pages.brands', compact('brands', 'counts', 'brand_covers'));
    }

    public function brand($id, Request $request, CartService $cartService)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $brand = Brands::where('business_id', $business_id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('name', $id);
            })
            ->firstOrFail();

        $request->merge(['brand' => $brand->id]);

        return app(ShopController::class)->index($request, $cartService);
    }

    public function promotions(Request $request)
    {
        return redirect()->route('ecommerce.shop', array_filter([
            'on_sale' => 1,
            'sort' => $request->get('sort', 'featured'),
            'q' => $request->get('q'),
            'brand' => $request->get('brand'),
            'category' => $request->get('category'),
        ]));
    }

    public function newArrivals(Request $request)
    {
        return redirect()->route('ecommerce.shop', array_filter([
            'new_only' => 1,
            'sort' => $request->get('sort', 'newest'),
            'q' => $request->get('q'),
            'brand' => $request->get('brand'),
            'category' => $request->get('category'),
            'on_sale' => $request->get('on_sale'),
        ]));
    }

    public function search(Request $request)
    {
        return redirect()->route('ecommerce.shop', array_filter(['q' => $request->get('q')]));
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:191']);
        $business_id = $request->attributes->get('ecommerce_business_id');

        if (Schema::hasTable('ecom_subscribers')) {
            EcomSubscriber::firstOrCreate(
                ['business_id' => $business_id, 'email' => $data['email']],
                ['source' => $request->input('source', 'footer')]
            );
        }

        return back()->with('success', "You're subscribed!");
    }

    public function brandRequest(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email',
            'message' => 'required|string|max:2000',
        ]);
        $this->storeInquiry($request, $data, 'brand_request');

        return back()->with('success', 'Brand request received. Our purchasing team will follow up.');
    }

    protected function storeInquiry(Request $request, array $data, string $source): void
    {
        if (! Schema::hasTable('ecom_inquiries')) {
            return;
        }
        EcomInquiry::create([
            'business_id' => $request->attributes->get('ecommerce_business_id'),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'source' => $source,
        ]);
    }

    protected function storeName(): string
    {
        $settings = request()->attributes->get('ecommerce_settings');

        return trim((string) ($settings->store_name ?? '')) ?: 'Wholesale Store';
    }

    protected function contactEmail(): string
    {
        $settings = request()->attributes->get('ecommerce_settings');
        if (! empty($settings?->contact_email)) {
            return (string) $settings->contact_email;
        }

        $business_id = request()->attributes->get('ecommerce_business_id');
        $location = $this->fulfillmentLocation($business_id);

        return (string) ($location->email ?? '');
    }

    protected function fulfillmentLocation($business_id): ?\App\BusinessLocation
    {
        $settings = request()->attributes->get('ecommerce_settings');
        if (! empty($settings?->location_id)) {
            $loc = \App\BusinessLocation::where('business_id', $business_id)->find($settings->location_id);
            if ($loc) {
                return $loc;
            }
        }

        return \App\BusinessLocation::where('business_id', $business_id)->Active()->orderBy('id')->first();
    }

    public function pages(): array
    {
        $settings = request()->attributes->get('ecommerce_settings');
        $business_id = request()->attributes->get('ecommerce_business_id');
        $store = $this->storeName();
        $email = $this->contactEmail();
        $extra = is_array($settings?->extra) ? $settings->extra : [];
        $business = $business_id ? \App\Business::find($business_id) : null;
        $location = $business_id ? $this->fulfillmentLocation($business_id) : null;

        $addressParts = array_filter([
            $location->landmark ?? null,
            $location->city ?? null,
            $location->state ?? null,
            $location->zip_code ?? null,
            $location->country ?? ($settings->default_country ?? null),
        ]);
        $address = implode(', ', $addressParts);
        $phone = $settings->contact_phone ?: ($location->mobile ?? $location->alternate_number ?? '');

        $aboutLede = trim((string) ($extra['about_lede'] ?? ''));
        if ($aboutLede === '') {
            $aboutLede = trim((string) ($settings->seo_description ?? ''));
        }
        if ($aboutLede === '') {
            $aboutLede = $store.' wholesale storefront with live inventory, bulk ordering, and account checkout.';
        }

        $aboutBody = trim((string) ($extra['about_body'] ?? ''));
        if ($aboutBody === '') {
            $lines = [
                $store.' is the wholesale storefront for '.($business->name ?? $store).'.',
                'Licensed retailers can browse live catalog inventory, place bulk orders, and manage account details online.',
            ];
            if ($address !== '') {
                $lines[] = 'Fulfillment location: '.$address.'.';
            }
            if ($phone !== '' || $email !== '') {
                $lines[] = 'Contact: '.trim(implode(' · ', array_filter([$phone, $email])));
            }
            $lines[] = 'Wholesale pricing and checkout unlock after your account and licenses are approved.';
            $aboutBody = implode("\n\n", $lines);
        }

        return [
            'about' => [
                'title' => trim((string) ($extra['about_title'] ?? '')) ?: ('About '.$store),
                'lede' => $aboutLede,
                'body' => $aboutBody,
            ],
            'faq' => [
                'title' => 'FAQ',
                'lede' => 'Answers for licensed retailers ordering from '.$store.'.',
                'body' => "Who can order?\nLicensed retailers with a verified wholesale account.\n\nHow do I get wholesale pricing?\nApply for an account and upload licenses. Pricing unlocks after approval.\n\nHow do I contact you?\nUse the contact form or email ".($email !== '' ? $email : 'the store').".",
            ],
            'terms' => [
                'title' => 'Terms of Service',
                'lede' => 'Wholesale terms for '.$store.' accounts.',
                'body' => "By creating an account you confirm you are authorized to purchase products for resale.\n\nOrders are wholesale only. Title transfers on shipment. Returns require authorization for unopened, saleable goods.\n\n{$store} may suspend accounts that fail compliance checks.",
            ],
            'privacy' => [
                'title' => 'Privacy Policy',
                'lede' => 'How '.$store.' handles retailer information.',
                'body' => "We collect business contact details, licenses, and order history to process wholesale accounts.\n\nWe do not sell personal information. Payment details are processed by the selected processor.\n\n".($email !== '' ? "Contact {$email} to request access or deletion of account data." : 'Contact the store to request access or deletion of account data.'),
            ],
            'shipping' => [
                'title' => 'Shipping Policy',
                'lede' => 'Shipping is calculated at checkout from your '.$store.' fulfillment location.',
                'body' => ($address !== '' ? "Orders ship from {$address}.\n\n" : '')."Shipping rates follow the rules configured for this store. Review your cart for the estimated shipping total before placing an order.\n\nDamaged freight claims should include photos and be reported promptly after delivery.",
            ],
            'returns' => [
                'title' => 'Returns',
                'lede' => 'Authorized returns for unopened wholesale goods.',
                'body' => "Request an RMA after delivery. Opened, used, or closeout items are not returnable unless required by law.\n\nRestocking fees may apply. Refunds are issued to the original payment method after inspection.",
            ],
            'compliance' => [
                'title' => 'Compliance',
                'lede' => 'Age-restricted wholesale requirements for '.$store.'.',
                'body' => "Wholesale accounts require business verification and any licenses required for the products you purchase.\n\nAge-restricted products are sold only to approved retailer accounts. Confirm shipping eligibility for your destination before ordering.",
            ],
            'wholesale-agreement' => [
                'title' => 'Wholesale Agreement',
                'lede' => 'Account terms for verified '.$store.' retailers.',
                'body' => "Approved accounts receive wholesale pricing, catalog access, and fulfillment support.\n\nYou agree to keep licenses current and to use this portal for wholesale purchasing only.\n\nNet-30 or other credit terms, if offered, require separate approval and may be revoked for late payment.",
            ],
        ];
    }
}

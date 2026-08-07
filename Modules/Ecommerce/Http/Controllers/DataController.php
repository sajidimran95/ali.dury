<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Utils\ModuleUtil;
use Menu;
use Modules\Ecommerce\Entities\EcommerceSetting;

class DataController
{
    public function product_form_fields()
    {
        return [
            'sales_channel',
            'ecom_slug',
            'ecom_short_description',
            'ecom_long_description',
            'ecom_seo_title',
            'ecom_seo_description',
            'ecom_is_featured',
            'ecom_is_new_arrival',
            'ecom_online_price',
            'ecom_discount_price',
        ];
    }

    public function product_form_part()
    {
        return [
            'template_path' => 'ecommerce::partials.product_form',
            'template_data' => [],
        ];
    }

    public function after_product_saved($data)
    {
        $product = $data['product'] ?? null;
        $request = $data['request'] ?? null;

        if (! $product || ! $request) {
            return;
        }

        $product->sales_channel = $request->input('sales_channel', $product->sales_channel ?? 'pos_only');
        $product->ecom_slug = $request->input('ecom_slug') ?: \Illuminate\Support\Str::slug($product->name).'-'.$product->id;
        $product->ecom_short_description = $request->input('ecom_short_description');
        $product->ecom_long_description = $request->input('ecom_long_description');
        $product->ecom_seo_title = $request->input('ecom_seo_title');
        $product->ecom_seo_description = $request->input('ecom_seo_description');
        $product->ecom_is_featured = $request->boolean('ecom_is_featured') ? 1 : 0;
        $product->ecom_is_new_arrival = $request->boolean('ecom_is_new_arrival') ? 1 : 0;
        $product->ecom_online_price = $request->input('ecom_online_price');
        $product->ecom_discount_price = $request->input('ecom_discount_price');

        if ($request->hasFile('ecom_gallery')) {
            $gallery = is_array($product->ecom_gallery) ? $product->ecom_gallery : [];
            foreach ((array) $request->file('ecom_gallery') as $file) {
                if (! $file) {
                    continue;
                }
                $name = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('uploads/img'), $name);
                $gallery[] = $name;
            }
            $product->ecom_gallery = $gallery;
        }

        $product->save();
    }

    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        if (empty($business_id)) {
            return;
        }

        $settings = EcommerceSetting::forBusiness($business_id);
        if (! $settings || ! $settings->is_enabled) {
            return;
        }

        if (! auth()->user()->can('dashboard.view') && ! auth()->user()->can('product.view')) {
            return;
        }

        Menu::modify('admin-sidebar-menu', function ($menu) {
            $isEcom = request()->segment(1) == 'ecommerce';
            $menu->dropdown(
                __('ecommerce::lang.ecommerce'),
                function ($sub) use ($isEcom) {
                    $sub->url(route('ecommerce.admin.dashboard'), __('ecommerce::lang.dashboard'), ['icon' => 'fa fas fa-tachometer-alt', 'active' => $isEcom && request()->segment(2) == 'dashboard']);
                    $sub->url(route('ecommerce.admin.products'), __('ecommerce::lang.products'), ['icon' => 'fa fas fa-boxes', 'active' => $isEcom && request()->segment(2) == 'products']);
                    $sub->url(action([\App\Http\Controllers\TaxonomyController::class, 'index']).'?type=product', __('ecommerce::lang.categories'), ['icon' => 'fa fas fa-tags']);
                    $sub->url(action([\App\Http\Controllers\BrandController::class, 'index']), __('ecommerce::lang.brands'), ['icon' => 'fa fas fa-copyright']);
                    $sub->url(route('ecommerce.admin.collections'), __('ecommerce::lang.collections'), ['icon' => 'fa fas fa-layer-group', 'active' => $isEcom && request()->segment(2) == 'collections']);
                    $sub->url(route('ecommerce.admin.banners'), __('ecommerce::lang.banners'), ['icon' => 'fa fas fa-images', 'active' => $isEcom && request()->segment(2) == 'banners']);
                    $sub->url(route('ecommerce.admin.customers'), __('ecommerce::lang.customers'), ['icon' => 'fa fas fa-users', 'active' => $isEcom && request()->segment(2) == 'customers']);
                    $sub->url(route('ecommerce.admin.orders'), __('ecommerce::lang.orders'), ['icon' => 'fa fas fa-shopping-bag', 'active' => $isEcom && request()->segment(2) == 'orders']);
                    $sub->url(route('ecommerce.admin.coupons'), __('ecommerce::lang.coupons'), ['icon' => 'fa fas fa-ticket-alt', 'active' => $isEcom && request()->segment(2) == 'coupons']);
                    $sub->url(route('ecommerce.admin.reviews'), __('ecommerce::lang.reviews'), ['icon' => 'fa fas fa-star', 'active' => $isEcom && request()->segment(2) == 'reviews']);
                    $sub->url(route('ecommerce.admin.reports'), __('ecommerce::lang.reports'), ['icon' => 'fa fas fa-chart-line', 'active' => $isEcom && request()->segment(2) == 'reports']);
                    $sub->url(route('ecommerce.admin.settings'), __('ecommerce::lang.settings'), ['icon' => 'fa fas fa-cog', 'active' => $isEcom && request()->segment(2) == 'settings']);
                },
                ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9h18l-1 11H4L3 9Z"/><path d="M8 9V7a4 4 0 0 1 8 0v2"/><path d="M9 13h6"/></svg>', 'active' => $isEcom]
            )->order(60);
        });
    }

    public function user_permissions()
    {
        return [
            [
                'value' => 'ecommerce.access',
                'label' => __('ecommerce::lang.ecommerce'),
                'default' => false,
            ],
        ];
    }
}

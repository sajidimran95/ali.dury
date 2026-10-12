<?php

namespace Modules\Ecommerce\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Ecommerce\Http\Middleware\EnsureEcommerceEnabled;

class EcommerceServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Ecommerce';

    protected string $moduleNameLower = 'ecommerce';

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        $router = $this->app['router'];
        $router->aliasMiddleware('ecommerce.enabled', EnsureEcommerceEnabled::class);

        $this->ensureModuleInstalled();
        $this->registerViewComposers();
    }

    protected function registerViewComposers(): void
    {
        view()->composer('ecommerce::front.*', function ($view) {
            try {
                $business_id = request()->attributes->get('ecommerce_business_id')
                    ?? view()->shared('ecom_business_id');

                if (! $business_id) {
                    return;
                }

                $cartService = app(\Modules\Ecommerce\Services\CartService::class);
                $cart = $cartService->getOrCreateCart((int) $business_id);
                $view->with('cart_totals', $cartService->totals($cart));
                $view->with('cart_product_ids', $cart->items->pluck('product_id')->unique()->values()->all());

                $nav_categories = \App\Category::where('business_id', $business_id)
                    ->where('category_type', 'product')
                    ->where(function ($q) {
                        $q->whereNull('parent_id')->orWhere('parent_id', 0);
                    })
                    ->with(['sub_categories' => function ($q) {
                        $q->where(function ($qq) {
                            $qq->where('category_type', 'product')->orWhereNull('category_type');
                        })->orderBy('name');
                    }])
                    ->orderBy('name')
                    ->limit(6)
                    ->get();

                $view->with('has_new_arrivals', \App\Product::storefrontQuery((int) $business_id)->where('ecom_is_new_arrival', 1)->exists());
                $view->with('has_promotions', \App\Product::storefrontQuery((int) $business_id)->where('ecom_is_featured', 1)->exists());

                // Brands + sample product image for mega menu tiles
                foreach ($nav_categories as $cat) {
                    $subIds = $cat->sub_categories->pluck('id')->all();
                    $catIds = array_merge([$cat->id], $subIds);

                    $brandRows = \App\Product::query()
                        ->where('business_id', $business_id)
                        ->whereIn('sales_channel', ['pos_ecommerce', 'ecommerce_only'])
                        ->where(function ($q) use ($catIds) {
                            $q->whereIn('category_id', $catIds)->orWhereIn('sub_category_id', $catIds);
                        })
                        ->whereNotNull('brand_id')
                        ->select('brand_id', 'image')
                        ->orderByDesc('id')
                        ->get()
                        ->groupBy('brand_id');

                    $megaBrands = collect();
                    foreach ($brandRows as $brandId => $rows) {
                        $brand = \App\Brands::find($brandId);
                        if (! $brand) {
                            continue;
                        }
                        $img = optional($rows->first(fn ($r) => ! empty($r->image)))->image;
                        $megaBrands->push((object) [
                            'id' => $brand->id,
                            'name' => $brand->name,
                            'image' => $img,
                        ]);
                        if ($megaBrands->count() >= 12) {
                            break;
                        }
                    }

                    // Fallback: any brands if category has none yet
                    if ($megaBrands->isEmpty()) {
                        $megaBrands = \App\Brands::where('business_id', $business_id)
                            ->orderBy('name')
                            ->limit(8)
                            ->get()
                            ->map(fn ($b) => (object) ['id' => $b->id, 'name' => $b->name, 'image' => null]);
                    }

                    $cat->setAttribute('mega_brands', $megaBrands);
                }

                $view->with('nav_categories', $nav_categories);
                $view->with('nav_brands', \App\Brands::where('business_id', $business_id)->orderBy('name')->limit(20)->get());
            } catch (\Throwable $e) {
                // keep existing shared defaults
            }
        });
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
    }

    public function registerViews(): void
    {
        $sourcePath = module_path($this->moduleName, 'Resources/views');
        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }

    public function registerTranslations(): void
    {
        $langPath = module_path($this->moduleName, 'Resources/lang');
        $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
    }

    /**
     * Auto-register system version so ModuleUtil treats this module as installed.
     */
    protected function ensureModuleInstalled(): void
    {
        try {
            if (! \Schema::hasTable('system')) {
                return;
            }

            $version = \App\System::getProperty('ecommerce_version');
            if (empty($version)) {
                \App\System::addProperty('ecommerce_version', config('ecommerce.module_version', '1.0.0'));
            }
        } catch (\Throwable $e) {
            // DB may not be ready during composer/artisan early boot
        }
    }
}

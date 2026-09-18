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

                $ecomProducts = \App\Product::query()
                    ->where('business_id', $business_id)
                    ->active()
                    ->forEcommerce()
                    ->select('id', 'category_id', 'sub_category_id', 'brand_id', 'ecom_is_new_arrival', 'ecom_is_featured')
                    ->get();

                $usedCatIds = $ecomProducts->pluck('category_id')
                    ->merge($ecomProducts->pluck('sub_category_id'))
                    ->filter()
                    ->unique()
                    ->values();

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
                    ->get()
                    ->filter(function ($cat) use ($usedCatIds) {
                        $isNewArrivals = strcasecmp(trim((string) $cat->name), 'New Arrivals') === 0;
                        if ($isNewArrivals && $cat->sub_categories->isNotEmpty()) {
                            return true;
                        }
                        if ($usedCatIds->contains($cat->id)) {
                            return true;
                        }

                        return $cat->sub_categories->contains(fn ($sub) => $usedCatIds->contains($sub->id));
                    })
                    ->values();

                $new_arrivals_category = $nav_categories->first(function ($cat) {
                    return strcasecmp(trim((string) $cat->name), 'New Arrivals') === 0;
                });
                if (! $new_arrivals_category) {
                    $new_arrivals_category = \App\Category::where('business_id', $business_id)
                        ->where('category_type', 'product')
                        ->where(function ($q) {
                            $q->whereNull('parent_id')->orWhere('parent_id', 0);
                        })
                        ->whereRaw('LOWER(name) = ?', ['new arrivals'])
                        ->with(['sub_categories' => function ($q) {
                            $q->orderBy('name');
                        }])
                        ->first();
                }

                $nav_categories = $nav_categories
                    ->reject(fn ($cat) => strcasecmp(trim((string) $cat->name), 'New Arrivals') === 0)
                    ->take(8)
                    ->values();

                $view->with('has_new_arrivals', $ecomProducts->contains(fn ($p) => (int) $p->ecom_is_new_arrival === 1) || $new_arrivals_category);
                $view->with('has_promotions', $ecomProducts->contains(fn ($p) => (int) $p->ecom_is_featured === 1));
                $view->with('new_arrivals_category', $new_arrivals_category);
                $view->with('nav_categories', $nav_categories);

                $ecomBrandIds = $ecomProducts->pluck('brand_id')->filter()->unique()->all();
                $view->with('nav_brands', $ecomBrandIds
                    ? \App\Brands::where('business_id', $business_id)->whereIn('id', $ecomBrandIds)->orderBy('name')->limit(20)->get()
                    : collect());
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

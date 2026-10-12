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
                $catalog = \Modules\Ecommerce\Support\StaticCatalog::all();
                $view->with('cart_totals', \Modules\Ecommerce\Support\StaticCart::totals());
                $view->with('cart_product_ids', \Modules\Ecommerce\Support\StaticCart::items()->pluck('product_id')->all());
                $view->with('nav_categories', \Modules\Ecommerce\Support\StaticCatalog::categories());
                $view->with('nav_brands', \Modules\Ecommerce\Support\StaticCatalog::brands());
                $view->with('has_new_arrivals', $catalog->contains(fn ($p) => $p->ecom_is_new_arrival));
                $view->with('has_promotions', $catalog->contains(fn ($p) => $p->ecom_is_featured));
                $view->with('new_arrivals_category', (object) [
                    'id' => 0,
                    'name' => 'New Arrivals',
                    'slug' => 'new-arrivals',
                    'sub_categories' => collect(),
                ]);
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

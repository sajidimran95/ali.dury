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
        view()->composer('ecommerce::front.layouts.store', function ($view) {
            try {
                $business_id = request()->attributes->get('ecommerce_business_id')
                    ?? view()->shared('ecom_business_id');

                if (! $business_id) {
                    return;
                }

                $cartService = app(\Modules\Ecommerce\Services\CartService::class);
                $cart = $cartService->getOrCreateCart((int) $business_id);
                $view->with('cart_totals', $cartService->totals($cart));
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

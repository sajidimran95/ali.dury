<?php

namespace Modules\Crm\Providers;

use Illuminate\Support\ServiceProvider;

class CrmServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Crm';

    protected string $moduleNameLower = 'crm';

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->ensureModuleInstalled();
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
        $this->loadViewsFrom(module_path($this->moduleName, 'Resources/views'), $this->moduleNameLower);
    }

    public function registerTranslations(): void
    {
        $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
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

            $version = \App\System::getProperty('crm_version');
            if (empty($version)) {
                \App\System::addProperty('crm_version', config('crm.module_version', '1.0.0'));
            }
        } catch (\Throwable $e) {
            // DB may not be ready during early boot
        }
    }
}

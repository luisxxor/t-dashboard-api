<?php

namespace Modules\PeruProperties\Providers;

use Modules\Common\Providers\CommonServiceProvider;

class PeruPropertiesServiceProvider extends CommonServiceProvider
{
    /**
     * @var string
     */
    protected $moduleName = 'PeruProperties';

    /**
     * @var string
     */
    protected $projectCode = 'pe-properties';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        parent::registerConfig();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register( RouteServiceProvider::class );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}

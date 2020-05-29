<?php

namespace Modules\DominicanaProperties\Providers;

use Modules\Common\Providers\CommonServiceProvider;

class DominicanaPropertiesServiceProvider extends CommonServiceProvider
{
    /**
     * @var string
     */
    protected $moduleName = 'DominicanaProperties';

    /**
     * @var string
     */
    protected $projectCode = 'do-properties';

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

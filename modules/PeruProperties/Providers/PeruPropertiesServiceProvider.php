<?php

namespace Modules\PeruProperties\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class PeruPropertiesServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
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
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        // scans up to one levels of directories
        foreach ( array_diff( scandir( module_path( 'PeruProperties', 'Config/' ) ), [ '.', '..' ] ) as $key => $value ) {
            if ( is_dir( module_path( 'PeruProperties', 'Config/' . $value ) ) === true ) {
                foreach ( array_diff( scandir( module_path( 'PeruProperties', 'Config/' . $value ) ), [ '.', '..' ] ) as $key => $subcontent ) {
                    $this->publishesConfig(
                        module_path( 'PeruProperties', 'Config/' . $value . '/' . $subcontent ),
                        config_path( 'peruproperties/' . $value . '/' . $subcontent ),
                        'peruproperties.' . $value . '.' . $subcontent
                    );
                }

                continue;
            }

            $this->publishesConfig(
                module_path( 'PeruProperties', 'Config/' . $value ),
                config_path( 'peruproperties/' . $value ),
                'peruproperties.' . $value
            );
        }

        config( [ 'database.connections.peru_properties' => config( 'peruproperties.database.connections.mongo' ) ] );
    }

    /**
     * Register paths to be published by the publish command
     * and merge the given configuration with the existing configuration.
     *
     * @param string $moduleConfigFilePath
     * @param string $configFilePath
     * @param string $configFileDotPath
     * @return void
     */
    protected function publishesConfig( string $moduleConfigFilePath, string $configFilePath, string $configFileDotPath )
    {
        $this->publishes( [ $moduleConfigFilePath => $configFilePath ] );
        $this->mergeConfigFrom( $moduleConfigFilePath, $configFileDotPath );
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

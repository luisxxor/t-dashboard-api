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
        // scans up to one level of directories
        foreach ( array_diff( scandir( module_path( 'PeruProperties', 'Config/' ) ), [ '.', '..' ] ) as $content ) {
            if ( is_dir( module_path( 'PeruProperties', 'Config/' . $content ) ) === true ) {
                foreach ( array_diff( scandir( module_path( 'PeruProperties', 'Config/' . $content ) ), [ '.', '..' ] ) as $subcontent ) {
                    $this->publishesConfig(
                        module_path( 'PeruProperties', 'Config/' . $content . '/' . $subcontent ),
                        config_path( 'pe-properties/' . $content . '/' . $subcontent ),
                        'pe-properties.' . $content . '.' . pathinfo( $subcontent, PATHINFO_FILENAME )
                    );
                }

                continue;
            }

            $this->publishesConfig(
                module_path( 'PeruProperties', 'Config/' . $content ),
                config_path( 'pe-properties/' . $content ),
                'pe-properties.' . pathinfo( $content, PATHINFO_FILENAME )
            );
        }

        config( [ 'database.connections.peru_properties' => config( 'pe-properties.database.connections.mongo' ) ] );
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

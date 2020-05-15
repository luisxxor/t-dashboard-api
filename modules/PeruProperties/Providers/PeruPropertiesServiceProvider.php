<?php

namespace Modules\PeruProperties\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class PeruPropertiesServiceProvider extends ServiceProvider
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
        foreach ( array_diff( scandir( module_path( $this->moduleName, 'Config/' ) ), [ '.', '..' ] ) as $content ) {
            if ( is_dir( module_path( $this->moduleName, 'Config/' . $content ) ) === true ) {
                foreach ( array_diff( scandir( module_path( $this->moduleName, 'Config/' . $content ) ), [ '.', '..' ] ) as $subcontent ) {
                    if ( $content === 'multi-api' ) {
                        $this->publishesConfig(
                            module_path( $this->moduleName, 'Config/' . $content . '/' . $subcontent ),
                            config_path( $content . '/' . $this->projectCode . '/' . $subcontent ),
                            $content . '.' . $this->projectCode . '.' . pathinfo( $subcontent, PATHINFO_FILENAME )
                        );

                        continue;
                    }

                    $this->publishesConfig(
                        module_path( $this->moduleName, 'Config/' . $content . '/' . $subcontent ),
                        config_path( $this->projectCode . '/' . $content . '/' . $subcontent ),
                        $this->projectCode . '.' . $content . '.' . pathinfo( $subcontent, PATHINFO_FILENAME )
                    );
                }

                continue;
            }

            $this->publishesConfig(
                module_path( $this->moduleName, 'Config/' . $content ),
                config_path( $this->projectCode . '/' . $content ),
                $this->projectCode . '.' . pathinfo( $content, PATHINFO_FILENAME )
            );
        }

        config( [ 'database.connections.' . $this->projectCode => config( $this->projectCode . '.database.connections.mongo' ) ] );
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

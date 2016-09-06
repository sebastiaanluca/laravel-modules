<?php

namespace Nwidart\Modules;

use Illuminate\Support\ServiceProvider;
use Laracademy\Generators\GeneratorsServiceProvider;
use Nwidart\Modules\Providers\BootstrapServiceProvider;
use Nwidart\Modules\Providers\ConsoleServiceProvider;
use Nwidart\Modules\Providers\ContractsServiceProvider;
use Nwidart\Modules\Support\Stub;

class LaravelModulesServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    
    /**
     * Publish required front-end build assets to the root app directory.
     */
    protected function registerPublishableResources()
    {
        $this->publishes([
            __DIR__ . '/../resources' => app_path(),
        ], 'build-scripts');
    }
    
    /**
     * Register all modules.
     */
    protected function registerModules()
    {
        $this->app->register(BootstrapServiceProvider::class);
    }
    
    /**
     * Register package's namespaces.
     */
    protected function registerNamespaces()
    {
        $configPath = __DIR__ . '/../config/config.php';
        $this->mergeConfigFrom($configPath, 'modules');
        $this->publishes([
            $configPath => config_path('modules.php'),
        ], 'config');
    }
    
    /**
     * Register the service provider.
     */
    protected function registerServices()
    {
        $this->app->singleton('modules', function($app) {
            $path = $app['config']->get('modules.paths.modules');
            
            return new Repository($app, $path);
        });
    }
    
    /**
     * Register providers.
     */
    protected function registerProviders()
    {
        $this->app->register(ConsoleServiceProvider::class);
        $this->app->register(ContractsServiceProvider::class);
    }
    
    /**
     * Booting the package.
     */
    public function boot()
    {
        $this->registerNamespaces();
        
        $this->registerModules();
    }
    
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerServices();
        $this->setupStubPath();
        $this->registerProviders();
        $this->registerPublishableResources();
    }
    
    /**
     * Setup stub path.
     */
    public function setupStubPath()
    {
        $this->app->booted(function($app) {
            Stub::setBasePath(__DIR__ . '/Commands/stubs');
            
            if ($app['modules']->config('stubs.enabled') === true) {
                Stub::setBasePath($app['modules']->config('stubs.path') ?: __DIR__ . '/Commands/stubs');
            }
        });
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['modules'];
    }
}

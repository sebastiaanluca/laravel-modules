<?php

namespace Nwidart\Modules\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use SebastiaanLuca\Helpers\Classes\ReflectionTrait;

// TODO: check if directories/files exist before using them
abstract class ModuleServiceProvider extends ServiceProvider
{
    use ReflectionTrait;
    
    /**
     * The (preferably lowercase) module name to use when publishing packages or loading resources.
     *
     * @var string
     */
    protected $module = '';
    
    /**
     * Automatically register and merge all configuration files found in the package with the ones
     * published by the user.
     */
    protected function registerConfiguration()
    {
        $files = app('Illuminate\Filesystem\Filesystem');
        $directory = $this->getClassDirectory() . '/../config';
        
        // Our package configuration files
        $configurations = $files->files($directory);
        
        // Merge each one with the published configuration
        // so we end up with complete configuration files
        foreach ($configurations as $configuration) {
            $this->mergeConfigFrom($configuration, $this->module);
            
            // TODO: should be this? namespace/module.configfile.key instead of namespace/module.key
            // Merge module config into app config (if any)
            // $this->mergeConfigFrom($configuration, $this->module . '/' . $configuration);  // $this->module . '/' . $configuration => should be app location of our module config
        }
    }
    
    /**
     * Prepare all module assets.
     */
    protected function bootResources()
    {
        // Load package views
        $this->loadViewsFrom($this->getClassDirectory() . '/../resources/views', $this->module);
        
        // Load package translations
        $this->loadTranslationsFrom($this->getClassDirectory() . '/../resources/lang', $this->module);
    }
    
    /**
     * Register all publishable module assets.
     */
    protected function registerPublishableResources()
    {
        // Specify what the user can publish
        $this->publishes([
            $this->getClassDirectory() . '/../config' => config_path($this->module)
        ], 'config');
        
        $this->publishes([
            $this->getClassDirectory() . '/../database/migrations' => database_path('migrations')
        ], 'migrations');
        
        $this->publishes([
            $this->getClassDirectory() . '/../resources/views' => base_path('resources/views/vendor/' . $this->module),
        ], 'views');
        
        $this->publishes([
            $this->getClassDirectory() . '/../resources/lang' => base_path('resources/lang/vendor/' . $this->module),
        ], 'translations');
        
        $this->publishes([
            $this->getClassDirectory() . '/../resources/public' => public_path('vendor/' . $this->module),
        ], 'public');
    }
    
    /**
     * Register package middleware.
     *
     * @param \Illuminate\Contracts\Http\Kernel $kernel
     * @param \Illuminate\Routing\Router $router
     */
    protected function bootMiddleware(Kernel $kernel, Router $router)
    {
        //
    }
    
    /**
     * Map out all module routes.
     */
    protected function mapRoutes()
    {
        //
    }
    
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerConfiguration();
    }
    
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // TODO: use a module.json file or something to list module dependencies and information
        // TODO: throw error if a dependency package was not found
        
        $this->bootResources();
        $this->registerPublishableResources();
        $this->bootMiddleware(app('Illuminate\Contracts\Http\Kernel'), app('router'));
        $this->mapRoutes();
    }
}
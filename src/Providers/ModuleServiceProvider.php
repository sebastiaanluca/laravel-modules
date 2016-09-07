<?php

namespace Nwidart\Modules\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

// TODO: check if directories/files exist before using them
abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * The (preferably lowercase) module name to use when publishing packages or loading resources.
     *
     * @var string
     */
    protected $module = '';
    
    /**
     * @var \Nwidart\Modules\Module
     */
    protected $instance;
    
    /**
     * Get the root path of the module.
     *
     * @return string
     */
    protected function getModulePath()
    {
        if (! $this->instance) {
            $this->instance = app('modules')->findOrFail($this->module);
        }
        
        return $this->instance->getPath();
    }
    
    /**
     * Automatically register and merge all configuration files found in the package with the ones
     * published by the user.
     */
    protected function registerConfiguration()
    {
        $files = app('Illuminate\Filesystem\Filesystem');
        $directory = $this->getModulePath() . '/config';
        
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
     * Bind concrete repositories to their interfaces.
     */
    protected function bindRepositories()
    {
        //
    }
    
    /**
     * Prepare all module assets.
     */
    protected function bootResources()
    {
        $this->loadMigrationsFrom($this->getModulePath() . '/database/migrations');
        $this->loadViewsFrom($this->getModulePath() . '/resources/views', $this->module);
        $this->loadTranslationsFrom($this->getModulePath() . '/resources/lang', $this->module);
    }
    
    /**
     * Register all publishable module assets.
     */
    protected function registerPublishableResources()
    {
        $this->publishes([
            $this->getModulePath() . '/config' => config_path($this->module)
        ], 'config');
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
        $this->bindRepositories();
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
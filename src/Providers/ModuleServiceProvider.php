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
    protected $module;
    
    /**
     * @var \Nwidart\Modules\Module
     */
    protected $instance;
    
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerConfiguration();
        $this->bindRepositories();
        $this->registerCommands();
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
        $moduleConfig = $this->getModulePath() . '/config/config.php';
        $userConfig = app('config')->get("modules.{$this->module}", []);
        
        // Full config
        $config = array_merge(require $moduleConfig, $userConfig);
        
        // Make the module configuration options available in the root space
        app('config')->set($this->module, $config);
    }
    
    /**
     * Bind concrete repositories to their interfaces.
     */
    protected function bindRepositories()
    {
        //
    }
    
    /**
     * Register artisan commands.
     */
    protected function registerCommands()
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
            $this->getModulePath() . '/config/config.php' => config_path("modules/{$this->module}.php")
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
}
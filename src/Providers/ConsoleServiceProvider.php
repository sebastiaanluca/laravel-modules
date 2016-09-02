<?php

namespace Nwidart\Modules\Providers;

use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Commands\ControllerCommand;
use Nwidart\Modules\Commands\GenerateDefaultScripts;
use Nwidart\Modules\Commands\GenerateDefaultViews;
use Nwidart\Modules\Commands\GenerateProviderCommand;
use Nwidart\Modules\Commands\GenerateResource;
use Nwidart\Modules\Commands\GenerateResourceViews;
use Nwidart\Modules\Commands\GenerateRouter;
use Nwidart\Modules\Commands\MakeCommand;
use Nwidart\Modules\Commands\MigrationCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    protected $defer = false;
    
    /**
     * The available commands
     *
     * @var array
     */
    protected $commands = [
        MakeCommand::class,
        
        GenerateProviderCommand::class,
        ControllerCommand::class,
        GenerateDefaultViews::class,
        GenerateDefaultScripts::class,
        
        GenerateResource::class,
        GenerateRouter::class,
        GenerateResourceViews::class,
        
        MigrationCommand::class,
        
        //        CommandCommand::class,
        //        DisableCommand::class,
        //        EnableCommand::class,
        //        GenerateEventCommand::class,
        //        GenerateListenerCommand::class,
        //        GenerateRouteProviderCommand::class,
        //        GenerateMiddlewareCommand::class,
        //        InstallCommand::class,
        //        ListCommand::class,
        //        MigrateCommand::class,
        //        MigrateRefreshCommand::class,
        //        MigrateResetCommand::class,
        //        MigrateRollbackCommand::class,
        //        ModelCommand::class,
        //        PublishCommand::class,
        //        PublishMigrationCommand::class,
        //        PublishTranslationCommand::class,
        //        SeedCommand::class,
        //        SeedMakeCommand::class,
        //        SetupCommand::class,
        //        UpdateCommand::class,
        //        UseCommand::class,
        //        DumpCommand::class,
        //        MakeRequestCommand::class,
        //        PublishConfigurationCommand::class,
        //        ModelCommand::class,
        //        PublishCommand::class,
        //        PublishMigrationCommand::class,
        //        PublishTranslationCommand::class,
        //        SeedCommand::class,
        //        SeedMakeCommand::class,
        //        SetupCommand::class,
        //        UpdateCommand::class,
        //        UseCommand::class,
        //        DumpCommand::class,
        //        MakeRequestCommand::class,
        //        PublishConfigurationCommand::class,
        //        GenerateJobCommand::class,
        //        GenerateMailCommand::class,
    ];
    
    /**
     * Register the commands.
     */
    public function register()
    {
        $this->commands($this->commands);
    }
    
    /**
     * @return array
     */
    public function provides()
    {
        $provides = $this->commands;
        
        return $provides;
    }
}

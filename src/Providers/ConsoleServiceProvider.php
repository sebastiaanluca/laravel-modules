<?php

namespace Nwidart\Modules\Providers;

use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Commands\ControllerCommand;
use Nwidart\Modules\Commands\GenerateDefaultScripts;
use Nwidart\Modules\Commands\GenerateDefaultViews;
use Nwidart\Modules\Commands\GenerateProviderCommand;
use Nwidart\Modules\Commands\GenerateResource;
use Nwidart\Modules\Commands\GenerateResourceRouter;
use Nwidart\Modules\Commands\GenerateResourceViews;
use Nwidart\Modules\Commands\MakeCommand;

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
        GenerateResourceRouter::class,
        GenerateResourceViews::class,
        
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
        //        MigrationCommand::class,
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
    ];
    
    /**
     * Register the commands.
     */
    public function register()
    {
        foreach ($this->commands as $command) {
            $this->commands($command);
        }
    }
    
    /**
     * @return array
     */
    public function provides()
    {
        $provides = [];
        
        foreach ($this->commands as $command) {
            $provides[] = $command;
        }
        
        return $provides;
    }
}

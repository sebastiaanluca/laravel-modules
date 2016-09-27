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
use Nwidart\Modules\Commands\MakeEntity;
use Nwidart\Modules\Commands\MakeRepository;
use Nwidart\Modules\Commands\MakeValidator;
use Nwidart\Modules\Commands\MigrationCommand;
use Nwidart\Modules\Commands\ModelCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * The available commands
     *
     * @var array
     */
    protected $commands = [
        MakeCommand::class,
        
        GenerateProviderCommand::class,
        
        MigrationCommand::class,
        ModelCommand::class,
        MakeEntity::class,
        MakeRepository::class,
        
        ControllerCommand::class,
        MakeValidator::class,
        GenerateRouter::class,

        GenerateDefaultScripts::class,
        GenerateDefaultViews::class,
        GenerateResourceViews::class,

        GenerateResource::class,
        
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

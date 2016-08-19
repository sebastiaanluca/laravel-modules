<?php

namespace Nwidart\Modules\Commands;

use Exception;
use Illuminate\Console\Command;
use Nwidart\Modules\Generators\ModuleGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:make';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new module.';
    
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::IS_ARRAY, 'The names of modules will be created.'],
        ];
    }
    
    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['plain', 'p', InputOption::VALUE_NONE, 'Generate a plain module (without some resources).'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when module already exist.'],
        ];
    }
    
    /**
     * @param string $name
     *
     * @throws \Exception
     */
    protected function validateName($name)
    {
        if (count(explode('/', $name)) === 2) {
            return;
        }
        
        throw new Exception('Module name needs to be in the "vendor/name" format.');
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $names = $this->argument('name');
        
        foreach ($names as $name) {
            $this->validateName($name);
            
            list($vendor, $name) = explode('/', $name);
            
            with(new ModuleGenerator($vendor, $name))
                ->setFilesystem($this->laravel['files'])
                ->setModuleManager($this->laravel['modules'])
                ->setConfig($this->laravel['config'])
                ->setConsole($this)
                ->setForce($this->option('force'))
                ->setPlain($this->option('plain'))
                ->generate();
        }
    
        $this->info('To use the module, add the service provider to your app.php configuration file and register it in composer.json to enable autoloading.');
    }
}

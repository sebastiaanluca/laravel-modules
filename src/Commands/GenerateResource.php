<?php

namespace Nwidart\Modules\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class GenerateResource extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'module:make:resource';
    
    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Generate all assets for a new resource.';
    
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['resource', InputArgument::REQUIRED, 'The singular name of the resource.'],
            ['module', InputArgument::REQUIRED, 'The name of the module to create the controller in.'],
        ];
    }
    
    /**
     * Execute the console command.
     */
    public function fire()
    {
        // TODO: create resource, repo, … based on migration
        
        $this->call('module:make:resource-controller', [
            'resource' => $this->argument('resource'),
            'module' => $this->argument('module'),
        ]);
        
        $this->call('module:make:resource-router', [
            'resource' => $this->argument('resource'),
            'module' => $this->argument('module'),
        ]);
        
        $this->call('module:make:views:resource', [
            'resource' => $this->argument('resource'),
            'module' => $this->argument('module'),
        ]);
        
        $this->info('Now register your resource router in the <mapRoutes> method of your module service provider.');
    }
}
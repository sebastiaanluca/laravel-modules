<?php

namespace Nwidart\Modules\Commands;

use Illuminate\Console\Command;

class GenerateResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make:resource
                            {resource : The singular name of the resource}
                            {module? : The name of the module to create the controller in}';
    
    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Generate all assets for a new resource.';
    
    /**
     * Execute the console command.
     */
    public function fire()
    {
        // TODO: create resource, repo, … based on migration
        
        $this->call('module:make:controller', [
            'resource' => $this->argument('resource'),
            'module' => $this->argument('module'),
        ]);
        
        $this->call('module:make:router', [
            'resource' => $this->argument('resource'),
            'module' => $this->argument('module'),
        ]);
        
        $this->call('module:make:views:resource', [
            'resource' => $this->argument('resource'),
            'module' => $this->argument('module'),
        ]);
        
        $this->info('Now register your resource router in the "mapRoutes" method of your module service provider.');
    }
}
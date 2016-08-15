<?php

namespace Nwidart\Modules\Commands;

use Symfony\Component\Console\Input\InputArgument;

class GenerateResourceViews extends MultiGeneratorCommand
{
    /**
     * The name of argument being used.
     *
     * @var string
     */
    protected $argumentName = 'module';
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:make:views:resource';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new set of default CRUD views for a resource in the given module.';
    
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
     * Get the files to generate.
     *
     * @return array
     */
    protected function getFiles()
    {
        $resource = $this->argument('resource') . 's';
        
        return [
            [
                'name' => 'index',
                'type' => 'page',
                'source' => 'views/crud/index.stub',
                'directory' => 'resources/views/pages/' . $resource,
                'overrideSource' => true,
            ],
            [
                'name' => 'create',
                'type' => 'page',
                'source' => 'views/crud/create.stub',
                'directory' => 'resources/views/pages/' . $resource,
                'overrideSource' => true,
            ],
            [
                'name' => 'show',
                'type' => 'page',
                'source' => 'views/crud/show.stub',
                'directory' => 'resources/views/pages/' . $resource,
                'overrideSource' => true,
            ],
            [
                'name' => 'edit',
                'type' => 'page',
                'source' => 'views/crud/edit.stub',
                'directory' => 'resources/views/pages/' . $resource,
                'overrideSource' => true,
            ],
        ];
    }
}
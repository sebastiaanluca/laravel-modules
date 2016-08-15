<?php

namespace Nwidart\Modules\Commands;

class GenerateDefaultViews extends MultiGeneratorCommand
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
    protected $name = 'module:make:views';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new set of default views in the given module.';
    
    /**
     * The files to generate.
     *
     * @var array
     */
    protected $files = [
        'layout' => '/views/layout.stub',
        'page' => '/views/page.stub',
    ];
}
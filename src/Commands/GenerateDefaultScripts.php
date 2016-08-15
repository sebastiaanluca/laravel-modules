<?php

namespace Nwidart\Modules\Commands;

class GenerateDefaultScripts extends MultiGeneratorCommand
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
    protected $name = 'module:make:assets';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new set of default assets in the given module.';
    
    /**
     * Get the files to generate.
     *
     * @return array
     */
    protected function getFiles()
    {
        return [
            [
                'type' => 'script',
                'source' => 'resources/script.stub',
            ],
            [
                'type' => 'style',
                'source' => 'resources/style.stub',
            ],
        ];
    }
}
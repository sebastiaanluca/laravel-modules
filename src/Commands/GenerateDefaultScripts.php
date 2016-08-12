<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Support\Stub;
use Symfony\Component\Console\Input\InputArgument;

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
     * The files to generate.
     *
     * @var array
     */
    protected $files = [
        'script' => ['/resources/script.stub', 'js'],
        'style' => ['/resources/style.stub', 'scss'],
    ];
    
    /**
     * @return string
     */
    protected function getScriptContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getFullyQualifiedName());
        
        return (new Stub($this->getStubName(), [
            'CLASS_NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getControllerName(),
            'MODULE' => $this->getFullyQualifiedName(),
            'RESOURCE' => strtolower($this->argument($this->argumentName) . 's'),
        ]))->render();
    }
    
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::OPTIONAL, 'The name of the module to create the controller in.'],
        ];
    }
    
    /**
     * Get the destination file path.
     *
     * @return string
     */
    //    public function getDestinationFilePath()
    //    {
    //        $path = $this->laravel['modules']->getModulePath($this->getFullyQualifiedName());
    //    
    ////        foreach ($this->files as list($stub, $extension)) {
    ////            ddd($stub, $extension);
    ////        }
    //        
    //        $target = $this->laravel['modules']->config('paths.generator.script');
    //        
    //        return $path . '/' . $target . '/' . $this->getControllerName() . '.php';
    //    }
}

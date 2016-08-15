<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;

class ControllerCommand extends GeneratorCommand
{
    use ModuleCommandTrait;
    
    /**
     * The name of argument being used.
     *
     * @var string
     */
    protected $argumentName = 'resource';
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:make:resource-controller';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new restful resource controller in the given module.';
    
    /**
     * Get the stub file name based on the plain option
     *
     * @return string
     */
    protected function getStubName()
    {
        return '/controller.stub';
    }
    
    /**
     * @return string
     */
    protected function getTemplateContents()
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
            [$this->argumentName, InputArgument::REQUIRED, 'The singular name of the resource.'],
            ['module', InputArgument::OPTIONAL, 'The name of the module to create the controller in.'],
        ];
    }
    
    /**
     * Get the resource controller name.
     *
     * @return string
     */
    protected function getControllerName()
    {
        return studly_case($this->argument($this->argumentName)) . 'Controller';
    }
    
    /**
     * Get the destination file path.
     *
     * @return string
     */
    public function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getFullyQualifiedName());
        
        $controllerPath = $this->laravel['modules']->config('paths.generator.controller');
        
        return $path . '/' . $controllerPath . '/' . $this->getControllerName() . '.php';
    }
    
    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace()
    {
        return 'Http\Controllers';
    }
}
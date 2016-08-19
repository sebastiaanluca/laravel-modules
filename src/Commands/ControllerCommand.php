<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;

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
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make:controller 
                            {resource : The singular name of the resource} 
                            {module? : The name of the module to create the controller in} 
                            {--plain : Create an empty controller instead of one with CRUD methods}';
    
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
        return $this->option('plain') ? 'controller-plain.stub' : 'controller.stub';
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
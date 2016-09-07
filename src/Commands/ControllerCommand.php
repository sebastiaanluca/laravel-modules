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
                            {--plain : Create an empty controller without CRUD methods}
                            {--repository : Implement the use of a repository}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new restful resource controller in the given module.';
    
    /**
     * @var array
     */
    protected $imports = [];
    
    /**
     * Get the destination file path.
     *
     * @return string
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getFullyQualifiedName());
        
        $controllerPath = $this->laravel['modules']->config('paths.generator.controller');
        
        return $path . '/' . $controllerPath . '/' . $this->getControllerName() . '.php';
    }
    
    /**
     * @return string
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getFullyQualifiedName());
        
        if ($this->option('repository')) {
            $this->imports[] = $this->getRepositoryClassName();
        }
        
        return (new Stub($this->getStubName(), [
            'CLASS_NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getControllerName(),
            'MODULE' => $this->getFullyQualifiedName(),
            'RESOURCE' => strtolower(str_plural($this->argument($this->argumentName))),
            'RESOURCE_SINGULAR' => strtolower($this->argument($this->argumentName)),
            'IMPORTS' => $this->getTemplateImports(),
            'REPOSITORY' => class_basename($this->getRepositoryClassName()),
            'NAMESPACED_REPOSITORY' => $this->getRepositoryClassName(),
        ]))->render();
    }
    
    /**
     * Get the stub file name based on the plain option
     *
     * @return string
     */
    protected function getStubName()
    {
        if ($this->option('plain')) {
            return 'controller-plain.stub';
        }
        
        if ($this->option('repository')) {
            return 'controller-repository.stub';
        }
        
        return 'controller.stub';
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
     * Get default namespace.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {
        return 'Http\Controllers';
    }
    
    /**
     * @return string
     */
    protected function getTemplateImports() : string
    {
        return collect($this->imports)->map(function($import) {
            return 'use ' . $import . ';' . PHP_EOL;
        })->implode('');
    }
    
    /**
     * Get the constructor template.
     *
     * @return string
     */
    protected function getRepositoryClassName()
    {
        return $this->getModule()->getNamespace() . '\Repositories\\' . $this->getClass() . 'Repository';
    }
}
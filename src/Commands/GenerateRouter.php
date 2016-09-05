<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Module;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;

class GenerateRouter extends GeneratorCommand
{
    use ModuleCommandTrait;
    
    /**
     * @var string
     */
    protected $argumentName = 'resource';
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make:router 
                            {resource : The singular name of the resource or the individual name of the router} 
                            {module? : The name of the module to create the router in} 
                            {--plain : Create an empty router}';
    
    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Generate a new router.';
    
    /**
     * Get the stub file name.
     *
     * @return string
     */
    protected function getStubName()
    {
        return $this->option('plain') ? 'router-plain.stub' : 'router.stub';
    }
    
    /**
     * Get default namespace.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {
        return 'Http\Routers';
    }
    
    /**
     * Get the name of the router.
     *
     * @return string
     */
    protected function getRouterName()
    {
        return studly_case($this->argument('resource')) . 'Router';
    }
    
    /**
     * Get the destination file path.
     *
     * @return string
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getFullyQualifiedName());
        
        $generatorPath = $this->laravel['modules']->config('paths.generator.router');
        
        return "{$path}/{$generatorPath}/{$this->getRouterName()}.php";
    }
    
    /**
     * Get template contents.
     *
     * @return string
     */
    protected function getTemplateContents()
    {
        /** @var Module $module */
        $module = $this->laravel['modules']->findOrFail($this->getFullyQualifiedName());
        $resource = strtolower($this->argument($this->argumentName));
        
        return (new Stub($this->getStubName(), [
            'CLASS_NAMESPACE' => $this->getClassNamespace($module),
            'MODULE_NAMESPACE' => $module->getNamespace(),
            'CONTROLLER' => studly_case($resource) . 'Controller',
            'RESOURCE' => $resource,
            'CLASS' => $this->getRouterName(),
        ]))->render();
    }
}
<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Module;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;

class GenerateResourceRouter extends GeneratorCommand
{
    use ModuleCommandTrait;
    
    /**
     * @var string
     */
    protected $argumentName = 'resource';
    
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'module:make:resource-router';
    
    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Generate a new resource router.';
    
    /**
     * The command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['resource', InputArgument::REQUIRED, 'The singular name of the resource.'],
            ['module', InputArgument::OPTIONAL, 'The name of the module.'],
        ];
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
        
        return (new Stub('/resource_router.stub', [
            'CLASS_NAMESPACE' => $this->getClassNamespace($module),
            'MODULE_NAMESPACE' => $module->getNamespace(),
            'CONTROLLER' => studly_case($resource) . 'Controller',
            'RESOURCE' => $resource,
            'CLASS' => $this->getRouterName(),
        ]))->render();
    }
    
    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace()
    {
        return 'Http\Routers';
    }
}

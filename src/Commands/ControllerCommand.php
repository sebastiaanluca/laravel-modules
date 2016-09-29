<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;

class ControllerCommand extends GeneratorCommand
{
    use ModuleCommandTrait;
    use UsesImportsAndTraits;
    
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
                            {--repository : Implement the use of a repository}
                            {--validators : Implement the use of form request validators}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new restful resource controller in the given module.';
    
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
        
        $resource = strtolower(str_plural($this->argument($this->argumentName)));
        
        $replacements = [
            'CLASS_NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getControllerName(),
            'MODULE' => $this->getFullyQualifiedName(),
            
            'RESOURCE' => $resource,
            'RESOURCE_SINGULAR' => strtolower($this->argument($this->argumentName)),
            
            'REPOSITORY' => class_basename($this->getRepositoryClassName()),
            'NAMESPACED_REPOSITORY' => $this->getRepositoryClassName(),
            
            'STORE_VALIDATOR_PHPDOC' => '',
            'STORE_VALIDATOR_PARAMETER' => '',
            
            'STORE_REPOSITORY_CALL' => '$this->' . $resource . '->create($request->all());',
            'UPDATE_REPOSITORY_CALL' => '$this->' . $resource . '->update($id, $request->all());',
        ];
        
        if ($this->option('repository')) {
            $this->imports[] = $this->getRepositoryClassName();
        }
        
        if ($this->usesValidators()) {
            $storeValidator = $this->getStoreValidatorClass();
            $updateValidator = $this->getUpdateValidatorClass();
            
            $this->imports[] = $storeValidator;
            $this->imports[] = $updateValidator;
            
            $replacements['STORE_VALIDATOR_PHPDOC'] = PHP_EOL . '     * @param \\' . $storeValidator . ' $validator';
            $replacements['STORE_VALIDATOR_PARAMETER'] = ', ' . class_basename($storeValidator) . ' $validator';
            $replacements['STORE_REPOSITORY_CALL'] = '$this->' . $resource . '->create($validator->valid());';
            
            $replacements['UPDATE_VALIDATOR_PHPDOC'] = PHP_EOL . '     * @param \\' . $updateValidator . ' $validator';
            $replacements['UPDATE_VALIDATOR_PARAMETER'] = ', ' . class_basename($updateValidator) . ' $validator';
            $replacements['UPDATE_REPOSITORY_CALL'] = '$this->' . $resource . '->update($id, $validator->valid());';
        }
        
        $replacements['IMPORTS'] = $this->getTemplateImports();
        
        return (new Stub($this->getStubName(), $replacements))->render();
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
     * Get the constructor template.
     *
     * @return string
     */
    protected function getRepositoryClassName() : string
    {
        return $this->getModule()->getNamespace() . '\Repositories\\' . $this->getClass() . 'Repository';
    }
    
    /**
     * @return bool
     */
    protected function usesValidators() : bool
    {
        return $this->option('validators');
    }
    
    /**
     * @return string
     */
    protected function getStoreValidatorClass() : string
    {
        return $this->getModule()->getNamespace() . '\Http\\Validators\\' . $this->getClass() . 'StoreValidator';
    }
    
    /**
     * @return string
     */
    protected function getUpdateValidatorClass() : string
    {
        return $this->getModule()->getNamespace() . '\Http\\Validators\\' . $this->getClass() . 'UpdateValidator';
    }
}
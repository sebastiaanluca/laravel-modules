<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Traits\ModuleCommandTrait;

class MakeRepository extends MultiGeneratorCommand
{
    use ModuleCommandTrait;
    
    /**
     * The name of argument name.
     *
     * @var string
     */
    protected $argumentName = 'model';
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make:repository
                            {model : The full namespace and name of the model}
                            {module : The name of the module to create the model in}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new Eloquent repository and interface in the given module.';
    
    /**
     * Get the name of the repository interface.
     *
     * @return string
     */
    protected function getInterfaceClassName()
    {
        return "{$this->getClass()}Repository";
    }
    
    /**
     * Get the name of the repository.
     *
     * @return string
     */
    protected function getRepositoryClassName()
    {
        return "{$this->getClass()}EloquentRepository";
    }
    
    /**
     * Get default namespace.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {
        return 'Repositories';
    }
    
    /**
     * Get the files to generate.
     *
     * @return array
     */
    protected function getFiles()
    {
        return [
            [
                'name' => $this->getInterfaceClassName(),
                'type' => 'repository',
                'source' => 'repository-interface.stub',
            ],
            [
                'name' => $this->getRepositoryClassName(),
                'type' => 'repository',
                'source' => 'repository-eloquent.stub',
            ],
        ];
    }
    
    /**
     * Get the stub's variables.
     *
     * @param array $file
     *
     * @return array
     */
    protected function getStubVariables(array $file)
    {
        return array_merge(parent::getStubVariables($file), [
            'INTERFACE' => $this->getInterfaceClassName(),
            'MODEL' => $this->argument('model'),
            'MODEL_BASENAME' => $this->getClass(),
        ]);
    }
    
    /**
     * Execute the console command.
     */
    public function fire()
    {
        parent::fire();
        
        $namespace = $this->getClassNamespace($this->getModule());
        
        $interface = '\\' . $namespace . '\\' . $this->getInterfaceClassName() . '::class';
        $repository = '\\' . $namespace . '\\' . $this->getRepositoryClassName() . '::class';
        
        $this->info('Now add the following line to the <bindRepositories> method in your module service provider:');
        $this->info('$this->app->bind(' . $interface . ', ' . $repository . ');');
    }
    
}

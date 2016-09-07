<?php

namespace Nwidart\Modules\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Exceptions\FileAlreadyExistException;
use Nwidart\Modules\Generators\FileGenerator;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;

abstract class MultiGeneratorCommand extends Command
{
    use ModuleCommandTrait;
    
    /**
     * The name of 'name' argument.
     *
     * @var string
     */
    protected $argumentName = '';
    
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'The name of the module to create the controller in.'],
        ];
    }
    
    /**
     * Get class name.
     *
     * @return string
     */
    protected function getClass()
    {
        return class_basename($this->argument($this->argumentName));
    }
    
    /**
     * Get default namespace.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {
        return '';
    }
    
    /**
     * Get class namespace.
     *
     * @param \Nwidart\Modules\Module $module
     *
     * @return string
     */
    protected function getClassNamespace($module)
    {
        $namespace = $module->getNamespace();
        $namespace .= '\\' . $this->getDefaultNamespace();
        
        return trim($namespace, '\\');
    }
    
    /**
     * Get template contents.
     *
     * @param string $stub
     * @param array $file
     *
     * @return string
     */
    protected function getRenderedStub($stub, array $file)
    {
        return (new Stub($stub, $this->getStubVariables($file)))->render();
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
        $module = $this->getModule();
        
        return [
            'MODULE' => $this->getFullyQualifiedName(),
            'MODULE_TITLE' => $module->getStudlyName(),
            'MODULE_NAME' => $module->getLowerName(),
            'NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => array_get($file, 'name', $this->getClass()),
        ];
    }
    
    /**
     * Get the destination file path.
     *
     * @param string $file
     *
     * @return string
     */
    protected function getDestinationFilePathFor($file)
    {
        $module = $this->getModule();
        
        $path = $this->laravel['modules']->getModulePath($module->getFullyQualifiedName());
        $target = $this->laravel['modules']->config('paths.generator.' . $file['type']);
        
        if (! is_array($target)) {
            $target = ['directory' => $target];
        }
        
        // $file values take precedence over user configuration
        $directory = array_get($file, 'directory') ?: array_get($target, 'directory', '');
        $name = array_get($file, 'name') ?: array_get($target, 'name', $module->getLowerName());
        $extension = array_get($file, 'extension') ?: array_get($target, 'extension', '.php');
        
        return "{$path}/{$directory}/{$name}{$extension}";
    }
    
    /**
     * @param string $file
     */
    protected function generateFile($file)
    {
        $path = str_replace('\\', '/', $this->getDestinationFilePathFor($file));
        
        if (! $this->laravel['files']->isDirectory($dir = dirname($path))) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }
        
        $stub = $this->laravel['modules']->config('paths.sources.' . $file['type'], $file['source']);
        
        if (array_get($file, 'overrideSource')) {
            $stub = array_get($file, 'source', $stub);
        }
        
        $contents = $this->getRenderedStub($stub, $file);
        
        try {
            with(new FileGenerator($path, $contents))->generate();
            
            $this->info("Created : {$path}");
        } catch (FileAlreadyExistException $e) {
            $this->error("File : {$path} already exists.");
        }
    }
    
    /**
     * Get the files to generate.
     *
     * @return array
     */
    abstract protected function getFiles();
    
    /**
     * Execute the console command.
     */
    public function fire()
    {
        foreach ($this->getFiles() as $file) {
            $this->generateFile($file);
        }
    }
}
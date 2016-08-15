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
     * The files to generate.
     *
     * @var array
     */
    protected $files = [];
    
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
        $extra = str_replace($this->getClass(), '', $this->argument($this->argumentName));
        $extra = str_replace('/', '\\', $extra);
        
        $namespace = $module->getNamespace();
        $namespace .= '\\' . $this->getDefaultNamespace();
        $namespace .= '\\' . $extra;
        
        return trim($namespace, '\\');
    }
    
    /**
     * Get template contents.
     *
     * @param string $stub
     *
     * @return string
     */
    protected function getTemplateContentsFor($stub)
    {
        $module = $this->getModule();
        
        return (new Stub($stub, [
            'MODULE' => $this->getFullyQualifiedName(),
            'MODULE_TITLE' => $module->getStudlyName(),
            'MODULE_NAME' => $module->getLowerName(),
        ]))->render();
    }
    
    /**
     * Get the destination file path.
     *
     * @param string $type
     *
     * @return string
     */
    protected function getDestinationFilePathFor($type)
    {
        $module = $this->getModule();
        
        $path = $this->laravel['modules']->getModulePath($module->getFullyQualifiedName());
        $target = $this->laravel['modules']->config('paths.generator.' . $type);
        
        if (! is_array($target)) {
            $target = ['directory' => $target];
        }
        
        $directory = array_get($target, 'directory', '');
        $name = array_get($target, 'name', $module->getLowerName());
        $extension = array_get($target, 'extension', '.php');
        
        return "{$path}/{$directory}/{$name}{$extension}";
    }
    
    
    /**
     * @param string $type
     * @param string $defaultSource
     */
    protected function generateFile($type, $defaultSource)
    {
        $path = str_replace('\\', '/', $this->getDestinationFilePathFor($type));
        
        if (! $this->laravel['files']->isDirectory($dir = dirname($path))) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }
        
        $stub = $this->laravel['modules']->config('paths.sources.' . $type, $defaultSource);
        
        $contents = $this->getTemplateContentsFor($stub);
        
        try {
            with(new FileGenerator($path, $contents))->generate();
            
            $this->info("Created : {$path}");
        } catch (FileAlreadyExistException $e) {
            $this->error("File : {$path} already exists.");
        }
    }
    
    /**
     * Execute the console command.
     */
    public function fire()
    {
        foreach ($this->files as $type => $defaultSource) {
            $this->generateFile($type, $defaultSource);
        }
    }
}
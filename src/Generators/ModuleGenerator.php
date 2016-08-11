<?php

namespace Nwidart\Modules\Generators;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Nwidart\Modules\Module;
use Nwidart\Modules\Repository;
use Nwidart\Modules\Support\Stub;

class ModuleGenerator extends Generator
{
    /**
     * @var string
     */
    protected $vendor;
    
    /**
     * The module name will created.
     *
     * @var string
     */
    protected $name;
    
    /**
     * The laravel config instance.
     *
     * @var Config
     */
    protected $config;
    
    /**
     * The laravel filesystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;
    
    /**
     * The laravel console instance.
     *
     * @var Console
     */
    protected $console;
    
    /**
     * The pingpong module instance.
     *
     * @var Module
     */
    protected $modules;
    
    /**
     * Force status.
     *
     * @var bool
     */
    protected $force = false;
    
    /**
     * Generate a plain module.
     *
     * @var bool
     */
    protected $plain = false;
    
    /**
     * The constructor.
     *
     * @param string $vendor
     * @param string $name
     * @param Repository $module
     * @param Config $config
     * @param Filesystem $filesystem
     * @param Console $console
     */
    public function __construct($vendor, $name, Repository $module = null, Config $config = null, Filesystem $filesystem = null, Console $console = null)
    {
        $this->vendor = $vendor;
        $this->name = $name;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->console = $console;
        $this->modules = $module;
    }
    
    /**
     * Get the contents of the specified stub file by given stub name.
     *
     * @param $stub
     *
     * @return string
     */
    protected function getStubContents($stub)
    {
        return (new Stub(
            '/' . $stub . '.stub',
            $this->getReplacement($stub))
        )->render();
    }
    
    /**
     * Get array replacement for the specified stub.
     *
     * @param $stub
     *
     * @return array
     */
    protected function getReplacement($stub)
    {
        $replacements = $this->modules->config('stubs.replacements');
        
        if (! isset($replacements[$stub])) {
            return [];
        }
        
        $keys = $replacements[$stub];
        
        $replaces = [];
        
        foreach ($keys as $key) {
            if (method_exists($this, $method = 'get' . ucfirst(studly_case(strtolower($key))) . 'Replacement')) {
                $replaces[$key] = call_user_func([$this, $method]);
            } else {
                $replaces[$key] = null;
            }
        }
        
        return $replaces;
    }
    
    /**
     * Get the module name in lower case.
     *
     * @return string
     */
    protected function getLowerNameReplacement()
    {
        return strtolower($this->getName());
    }
    
    /**
     * Get the module name in studly case.
     *
     * @return string
     */
    protected function getStudlyNameReplacement()
    {
        return $this->getName();
    }
    
    /**
     * Get replacement for $VENDOR$.
     *
     * @return string
     */
    protected function getVendorReplacement()
    {
        return studly_case($this->getVendor());
    }
    
    /**
     * Get replacement for $AUTHOR_NAME$.
     *
     * @return string
     */
    protected function getAuthorNameReplacement()
    {
        return $this->modules->config('composer.author.name');
    }
    
    /**
     * Get replacement for $AUTHOR_EMAIL$.
     *
     * @return string
     */
    protected function getAuthorEmailReplacement()
    {
        return $this->modules->config('composer.author.email');
    }
    
    /**
     * Set plain flag.
     *
     * @param bool $plain
     *
     * @return $this
     */
    public function setPlain($plain)
    {
        $this->plain = $plain;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getVendor()
    {
        return Str::studly($this->vendor);
    }
    
    /**
     * Get the name of module will created. By default in studly case.
     *
     * @return string
     */
    public function getName()
    {
        return Str::studly($this->name);
    }
    
    /**
     * @return string
     */
    public function getVendorNamespace()
    {
        return strtolower(snake_case($this->getVendor()) . '/' . snake_case($this->getName()));
    }
    
    /**
     * @return string
     */
    public function getSourcePath()
    {
        return $this->getVendorNamespace() . '/src';
    }
    
    /**
     * Get the laravel config instance.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * Set the laravel config instance.
     *
     * @param Config $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;
        
        return $this;
    }
    
    /**
     * Get the laravel filesystem instance.
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }
    
    /**
     * Set the laravel filesystem instance.
     *
     * @param Filesystem $filesystem
     *
     * @return $this
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;
        
        return $this;
    }
    
    /**
     * Get the laravel console instance.
     *
     * @return Console
     */
    public function getConsole()
    {
        return $this->console;
    }
    
    /**
     * Set the laravel console instance.
     *
     * @param Console $console
     *
     * @return $this
     */
    public function setConsole($console)
    {
        $this->console = $console;
        
        return $this;
    }
    
    /**
     * Get the pingpong module instance.
     *
     * @return Module
     */
    public function getModuleManager()
    {
        return $this->modules;
    }
    
    /**
     * Set the pingpong module instance.
     *
     * @param mixed $manager
     *
     * @return $this
     */
    public function setModuleManager($manager)
    {
        $this->modules = $manager;
        
        return $this;
    }
    
    /**
     * Get the list of folders will created.
     *
     * @return array
     */
    public function getFolders()
    {
        return array_values($this->modules->config('paths.generator'));
    }
    
    /**
     * Get the list of files will created.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->modules->config('stubs.files');
    }
    
    /**
     * Set force status.
     *
     * @param bool|int $force
     *
     * @return $this
     */
    public function setForce($force)
    {
        $this->force = $force;
        
        return $this;
    }
    
    /**
     * Generate the module.
     */
    public function generate()
    {
        $namespace = $this->getVendorNamespace();
        
        if ($this->modules->has($namespace)) {
            if ($this->force) {
                $this->modules->delete($namespace);
            } else {
                $this->console->error("Module [{$namespace}] already exist!");
                
                return;
            }
        }
        
        $this->generateFolders();
        
        $this->generateFiles();
        
        if (! $this->plain) {
            $this->generateResources();
        }
        
        $this->console->info("Module [{$namespace}] created successfully.");
    }
    
    /**
     * Generate the folders.
     */
    public function generateFolders()
    {
        foreach ($this->getFolders() as $folder) {
            $path = $this->modules->getModulePath($this->getVendorNamespace()) . '/' . $folder;
            
            $this->filesystem->makeDirectory($path, 0755, true);
            
            // $this->generateGitKeep($path);
        }
    }
    
    /**
     * Generate git keep to the specified path.
     *
     * @param string $path
     */
    public function generateGitKeep($path)
    {
        $this->filesystem->put($path . '/.gitkeep', '');
    }
    
    /**
     * Generate the files.
     */
    public function generateFiles()
    {
        foreach ($this->getFiles() as $stub => $file) {
            $path = $this->modules->getModulePath($this->getVendorNamespace()) . '/' . $file;
            
            if (! $this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }
            
            $this->filesystem->put($path, $this->getStubContents($stub));
            
            $this->console->info("Created : {$path}");
        }
    }
    
    /**
     * Generate some resources.
     */
    public function generateResources()
    {
        //        $this->console->call('module:make-seed', [
        //            'name' => $this->getName(),
        //            'module' => $this->getName(),
        //            '--master' => true,
        //        ]);
        
        $this->console->call('module:make-provider', [
            'name' => $this->getName() . 'ServiceProvider',
            'module' => $this->getVendorNamespace(),
            '--master' => true,
        ]);
        
        //        $this->console->call('module:make-controller', [
        //            'controller' => $this->getName() . 'Controller',
        //            'module' => $this->getName(),
        //        ]);
    }
    
    /**
     * get the list for the replacements.
     */
    public function getReplacements()
    {
        return $this->modules->config('stubs.replacements');
    }
}

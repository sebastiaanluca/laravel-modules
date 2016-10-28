<?php

namespace Nwidart\Modules\Generators;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Nwidart\Modules\Repository;
use Nwidart\Modules\Support\Stub;

class ModuleGenerator extends Generator
{
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
    protected $module;
    
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
     * @param $name
     * @param Repository $module
     * @param Config $config
     * @param Filesystem $filesystem
     * @param Console $console
     */
    public function __construct($name, Repository $module = null, Config $config = null, Filesystem $filesystem = null, Console $console = null)
    {
        $this->name = $name;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->console = $console;
        $this->module = $module;
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
     * Get the name of module will created. By default in studly case.
     *
     * @return string
     */
    public function getName()
    {
        return Str::studly($this->name);
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
    public function getModule()
    {
        return $this->module;
    }
    
    /**
     * Set the pingpong module instance.
     *
     * @param mixed $module
     *
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;
        
        return $this;
    }
    
    /**
     * Get the list of folders will created.
     *
     * @return array
     */
    public function getFolders()
    {
        return array_values($this->module->config('paths.generator'));
    }
    
    /**
     * Get the list of files will created.
     *
     * @return array
     */
    public function getFiles()
    {
        // TODO: move to a different config key
        return $this->module->config('stubs.files');
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
        $name = $this->getName();
        
        if ($this->module->has($name)) {
            if ($this->force) {
                $this->module->delete($name);
            } else {
                $this->console->error("Module [{$name}] already exist!");
                
                return;
            }
        }
        
        // Scaffold the module structure
        $this->generateFolders();
        
        // Add default stubs
        $this->generateFiles();
        
        $this->console->info("Module [{$name}] created successfully.");
    }
    
    /**
     * Generate the folders.
     */
    public function generateFolders()
    {
        foreach ($this->getFolders() as $folder) {
            $path = $this->module->getModulePath($this->getName()) . '/' . $folder;
            
            $this->filesystem->makeDirectory($path, 0755, true);
            
            //            $this->generateGitKeep($path);
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
            $path = $this->module->getModulePath($this->getName()) . $file;
            
            if (! $this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }
            
            $this->filesystem->put($path, $this->getStubContents($stub));
            
            $this->console->info("Created: {$path}");
        }
        
        // TODO: update service provider stub to extend base module service provider
        // Add a default service provider
        $this->console->call('module:make-provider', [
            'name' => $this->getName() . 'ServiceProvider',
            'module' => $this->getName(),
            '--master' => true,
        ]);
    }
    
    /**
     * Get the contents of the specified stub file by given stub name.
     *
     * @param $stub
     *
     * @return Stub
     */
    protected function getStubContents($stub)
    {
        return (new Stub(
            '/' . $stub . '.stub',
            $this->getReplacements())
        )->render();
    }
    
    /**
     * Get array replacement for the specified stub.
     *
     * @return array
     */
    protected function getReplacements()
    {
        $keys = ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'VENDOR', 'AUTHOR_NAME', 'AUTHOR_EMAIL'];
        
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
        return $this->module->config('composer.vendor');
    }
    
    /**
     * Get replacement for $MODULE_NAMESPACE$.
     *
     * @return string
     */
    protected function getModuleNamespaceReplacement()
    {
        if (! $namespace = $this->module->config('namespace')) {
            return '';
        }
        
        return str_replace('\\', '\\\\', $namespace);
    }
    
    /**
     * Get replacement for $AUTHOR_NAME$.
     *
     * @return string
     */
    protected function getAuthorNameReplacement()
    {
        return $this->module->config('composer.author.name');
    }
    
    /**
     * Get replacement for $AUTHOR_EMAIL$.
     *
     * @return string
     */
    protected function getAuthorEmailReplacement()
    {
        return $this->module->config('composer.author.email');
    }
}

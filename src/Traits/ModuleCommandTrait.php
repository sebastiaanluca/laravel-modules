<?php

namespace Nwidart\Modules\Traits;

trait ModuleCommandTrait
{
    /**
     * @return \Nwidart\Modules\Module
     */
    public function getModule()
    {
        $module = $this->argument('module') ?: app('modules')->getUsedNow();
        
        return app('modules')->findOrFail($module);
    }
    
    /**
     * Get the module name.
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->getModule()->getStudlyName();
    }
    
    /**
     * @return string
     */
    public function getVendorNamespace()
    {
        return $this->argument('module') ?: $this->getModule()->getVendorNamespace();
    }
}

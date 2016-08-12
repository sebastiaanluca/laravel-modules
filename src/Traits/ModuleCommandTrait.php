<?php

namespace Nwidart\Modules\Traits;

trait ModuleCommandTrait
{
    /**
     * @return string
     */
    public function getActiveModuleIdentifier()
    {
        return $this->argument('module') ?: app('modules')->getUsedNow();
    }
    
    /**
     * @return \Nwidart\Modules\Module
     */
    public function getModule()
    {
        return app('modules')->findOrFail($this->getActiveModuleIdentifier());
    }
    
    /**
     * @return string
     */
    public function getFullyQualifiedName()
    {
        return $this->getModule()->getFullyQualifiedName();
    }
}

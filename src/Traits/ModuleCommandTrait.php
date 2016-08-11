<?php

namespace Nwidart\Modules\Traits;

trait ModuleCommandTrait
{
    /**
     * @return string
     */
    public function getFullyQualifiedName()
    {
        $module = $this->argument('module') ?: app('modules')->getUsedNow();
        
        /** @var \Nwidart\Modules\Module $module */
        $module = app('modules')->findOrFail($module);
        
        return $module->getFullyQualifiedName();
    }
}

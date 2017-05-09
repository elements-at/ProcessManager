<?php

namespace Elements\Bundle\ProcessManagerBundle\Executor;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Tool\Console;

class ClassMethod extends AbstractExecutor
{
    protected $name = 'classMethod';
    protected $extJsClass = 'pimcore.plugin.processmanager.executor.class.classMethod';

    /**
     * @param string[] $callbackSettings
     * @param null | MonitoringItem $monitoringItem
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        return Console::getPhpCli() . ' ' . realpath(PIMCORE_PROJECT_ROOT . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'console') . ' process-manager:class-method-executor -v';
    }
}

<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Tool\Console;

class ClassMethod extends AbstractExecutor
{
    protected string $name = 'classMethod';

    protected string $extJsClass = 'pimcore.plugin.processmanager.executor.class.classMethod';

    /**
     * @param string[] $callbackSettings
     * @param null | MonitoringItem $monitoringItem
     *
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        $command =  Console::getPhpCli() . ' ' . realpath(PIMCORE_PROJECT_ROOT . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'console') . ' process-manager:class-method-executor -v';

        if($monitoringItem instanceof \Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem) {
            $command .= ' --monitoring-item-id='.$monitoringItem->getId();
        }

        return $command;
    }
}

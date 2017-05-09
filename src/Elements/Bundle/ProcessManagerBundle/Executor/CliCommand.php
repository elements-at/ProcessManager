<?php

namespace Elements\Bundle\ProcessManagerBundle\Executor;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

class CliCommand extends AbstractExecutor
{
    protected $name = 'cliCommand';
    protected $extJsClass = 'pimcore.plugin.processmanager.executor.class.command';

    /**
     * @param string[] $callbackSettings
     * @param null | MonitoringItem $monitoringItem
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        return $this->getValues()['command'];
    }
}
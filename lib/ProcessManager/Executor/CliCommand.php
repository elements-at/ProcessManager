<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 22.06.2016
 * Time: 14:16
 */

namespace ProcessManager\Executor;

class CliCommand extends AbstractExecutor
{
    protected $name = 'cliCommand';
    protected $extJsConfigurationClass = 'pimcore.plugin.processmanager.executor.class.command';

    /**
     * @param string[] $callbackSettings
     * @param null | \ProcessManager\MonitoringItem $monitoringItem
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        return $this->getValues()['command'];
    }
}
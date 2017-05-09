<?php

namespace Elements\Bundle\ProcessManagerBundle\Executor;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Tool\Console;

class PimcoreCommand extends AbstractExecutor
{
    protected $name = 'pimcoreCommand';
    protected $extJsClass = 'pimcore.plugin.processmanager.executor.class.pimcoreCommand';

    /**
     * @param string[] $callbackSettings
     * @param null | MonitoringItem $monitoringItem
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        $options = $this->getValues()['commandOptions'];
        $options = str_replace('|', '', trim($options));
        $command = Console::getPhpCli() . ' ' . realpath(PIMCORE_PROJECT_ROOT . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'console') . ' ' . $this->getValues()['command'];

        if ($options) {
            $command .= ' ' . $options;
        }
        return $command;
    }
}
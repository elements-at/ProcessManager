<?php

namespace Elements\Bundle\ProcessManagerBundle\Executor;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

class ExportToolkit extends AbstractExecutor
{
    protected $name = 'exportToolkit';
    protected $extJsClass = 'pimcore.plugin.processmanager.executor.class.exportToolkit';

    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!$this->config['jobs']) {
            if (\Pimcore\Tool::classExists('ExportToolkit_Configuration')) {
                $list = \ExportToolkit_Configuration::getList();
                $this->config['jobs'] = array_keys($list);
            }
        }
    }

    /**
     * @param string[] $callbackSettings
     * @param null | MonitoringItem $monitoringItem
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        $command = \Pimcore\Tool\Console::getPhpCli() . ' ' . PIMCORE_PROJECT_ROOT . '/pimcore/cli/console.php export-toolkit:export --config-name=' . $this->getValues()['configName'];
        return $command;
    }
}
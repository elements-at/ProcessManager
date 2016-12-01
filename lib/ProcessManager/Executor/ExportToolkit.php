<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 22.06.2016
 * Time: 14:16
 */

namespace ProcessManager\Executor;

class ExportToolkit extends AbstractExecutor
{
    protected $name = 'exportToolkit';
    protected $extJsConfigurationClass = 'pimcore.plugin.processmanager.executor.class.exportToolkit';

    /**
     * @param string[] $callbackSettings
     * @param null | \ProcessManager\MonitoringItem $monitoringItem
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        $command = \Pimcore\Tool\Console::getPhpCli().' ' . PIMCORE_DOCUMENT_ROOT.'/pimcore/cli/console.php export-toolkit:export --config-name=' . $this->getValues()['configName'];
        return $command;
    }
}
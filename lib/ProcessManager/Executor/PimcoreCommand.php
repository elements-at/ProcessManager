<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 22.06.2016
 * Time: 14:16
 */

namespace ProcessManager\Executor;

class PimcoreCommand extends AbstractExecutor
{
    protected $name = 'pimcoreCommand';
    protected $extJsClass = 'pimcore.plugin.processmanager.executor.class.pimcoreCommand';

    /**
     * @param string[] $callbackSettings
     * @param null | \ProcessManager\MonitoringItem $monitoringItem
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        $options = $this->getValues()['commandOptions'];
        $options = str_replace('|','',trim($options));
        $command = \Pimcore\Tool\Console::getPhpCli().' ' . PIMCORE_DOCUMENT_ROOT.'/pimcore/cli/console.php ' . $this->getValues()['command'];

        if($options){
            $command .= ' ' . $options;
        }
        return $command;
    }
}
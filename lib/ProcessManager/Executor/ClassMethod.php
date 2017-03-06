<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 22.06.2016
 * Time: 14:16
 */

namespace ProcessManager\Executor;

class ClassMethod extends AbstractExecutor
{
    protected $name = 'classMethod';
    protected $extJsClass = 'pimcore.plugin.processmanager.executor.class.classMethod';

    /**
     * @param string[] $callbackSettings
     * @param null | \ProcessManager\MonitoringItem $monitoringItem
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        return \Pimcore\Tool\Console::getPhpCli().' ' . PIMCORE_DOCUMENT_ROOT.'/pimcore/cli/console.php process-manager:class-method-executor -v';
    }
}
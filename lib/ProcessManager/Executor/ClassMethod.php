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
    protected $extJsConfigurationClass = 'pimcore.plugin.processmanager.executor.classMethod';

    public function getCommand()
    {
        return \Pimcore\Tool\Console::getPhpCli().' ' . PIMCORE_DOCUMENT_ROOT.'/pimcore/cli/console.php process-manager:class-method-executor -v';
    }
}
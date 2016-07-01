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
    protected $extJsConfigurationClass = 'pimcore.plugin.processmanager.executor.pimcoreCommand';

    public function getCommand()
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
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
    protected $extJsConfigurationClass = 'pimcore.plugin.processmanager.executor.command';

    public function getCommand()
    {
        return $this->getValues()['command'];
    }
}
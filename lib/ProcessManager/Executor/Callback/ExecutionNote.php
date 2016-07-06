<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 27.06.2016
 * Time: 11:49
 */
namespace ProcessManager\Executor\Callback;

class ExecutionNote extends AbstractCallback {
    public $extJsClass = 'pimcore.plugin.processmanager.executor.callback.executionNote';

    public $name = 'executionNote';
}
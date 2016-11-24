<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 27.06.2016
 * Time: 11:49
 */
namespace ProcessManager\Executor\Callback;

/**
 * Class General
 *
 * Pass extJsClass and name in the configuration
 *
 * e.g.:
'\ProcessManager\Executor\Callback\General' => [
    'extJsClass' => 'pimcore.plugin.myprojetct.processmanager.executor.callback.customExporter',
    'name' => 'exportEasyCatalog'
]
 *
 * @package ProcessManager\Executor\Callback
 */
class General extends AbstractCallback {

    public function __construct($config)
    {
        parent::__construct($config);
        if(!$this->getExtJsClass()){
            throw new \Exception("Please set the extJsClass");
        }
        if(!$this->getName()){
            throw new \Exception("Please set a 'name'");
        }
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 22.06.2016
 * Time: 14:17
 */

namespace ProcessManager\Executor;

use ProcessManager\Configuration;

abstract class AbstractExecutor implements \JsonSerializable{

    protected $name = '';



    protected $extJsClass = '';

    protected $values = [];

    protected $loggers = [];

    protected $executorConfig = [];

    protected $actions = [];

    protected $isShellCommand = false;
    /**
     * @var \ProcessManager\Configuration
     */
    protected $config;

    public function __construct($config = []){
        $this->config = $config;
    }

    /**
     * @return boolean
     */
    public function getIsShellCommand()
    {
        return $this->isShellCommand;
    }

    /**
     * @param boolean $isShellCommand
     * @return $this
     */
    public function setIsShellCommand($isShellCommand)
    {
        $this->isShellCommand = $isShellCommand;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if(!$this->name){
            $this->name = lcfirst(array_pop(explode('\\',get_class($this))));
        }
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \ProcessManager\Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param \ProcessManager\Configuration $config
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return string
     */
    public function getExtJsClass()
    {
        return $this->extJsClass;
    }

    /**
     * @param string $extJsClass
     * @return $this
     */
    public function setExtJsClass($extJsClass)
    {
        $this->extJsClass= $extJsClass;
        return $this;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function setValues($values)
    {
        $this->values = $values;
        return $this;
    }

    public function getExtJsSettings(){

        $executorConfig = [
            'extJsClass' => $this->getExtJsClass(),
            'name' => $this->getName(),
            'class' => $this->getConfig()->getExecutorClass(),
        ];
        $data['executorConfig'] = $executorConfig;

        $data['values'] = $this->getValues();
        $data['loggers'] = $this->getLoggers();
        $data['actions'] = $this->getActions();

        foreach((array)$data['actions'] as $i => $actionData){
            $className = $actionData['class'];
            $x = new $className();
            $data['actions'][$i]['extJsClass'] = $x->getExtJsClass();
            $data['actions'][$i]['config'] = $x->getConfig();
        }

        foreach((array)$data['loggers'] as $i => $loggerData){
            $className = $loggerData['class'];
            $x = new $className();
            $data['loggers'][$i]['extJsClass'] = $x->getExtJsClass();
            $data['loggers'][$i]['config'] = $x->getConfig();
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param array $actions
     * @return $this
     */
    public function setActions($actions)
    {
        $this->actions = $actions;
        return $this;
    }


    /**
     *
     * Tests
     *
     * @param \ProcessManager\MonitoringItem $monitoringItem
     * @return string
     *
     */
    public function getShellCommand(\ProcessManager\MonitoringItem $monitoringItem){
        return \Pimcore\Tool\Console::getPhpCli().' ' . PIMCORE_DOCUMENT_ROOT.'/pimcore/cli/console.php process-manager:execute-shell-cmd --monitoring-item-id='.$monitoringItem->getId();
    }


    /**
     * returns the command which should be executed
     *
     * the CallbackSettings are only passed at execution time
     * @param string[] $callbackSettings
     * @param null | \ProcessManager\MonitoringItem $monitoringItem
     * @return mixed
     */
    abstract function getCommand($callbackSettings = [], $monitoringItem = null);


    public function jsonSerialize() {
        $values = array_merge(['class' => get_class($this)],get_object_vars($this));
        return $values;
    }

    /**
     * @return array
     */
    public function getLoggers()
    {
        return $this->loggers;
    }

    /**
     * @param array $loggers
     * @return $this
     */
    public function setLoggers($loggers)
    {
        $this->loggers = $loggers;
        return $this;
    }


    public function getStorageValue(){
        $data = [
            'values' => (array)$this->getValues(),
            'actions' => (array)$this->getActions(),
            'loggers' => (array)$this->getLoggers()
        ];
        return json_encode($data);
    }

    protected function setData($values){
        foreach($values as $key => $value){
            $setter = "set" . ucfirst($key);
            if(method_exists($this,$setter)){
                $this->$setter($value);
            }
        }
        return $this;
    }
    /**
     * @param \ProcessManager\Configuration $configuration
     * @return \ProcessManager\Configuration
     */
    public function setDataFromResource(\ProcessManager\Configuration $configuration){
        $settings = $configuration->getExecutorSettings();
        if(is_string($settings)){
            $this->setData(\Zend_Json::decode($settings));
        }
        $this->setConfig($configuration);
        return $configuration;
    }

}
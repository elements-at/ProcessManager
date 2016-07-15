<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 22.06.2016
 * Time: 14:17
 */

namespace ProcessManager\Executor;

abstract class AbstractExecutor {

    protected $name = '';

    protected $config = [];

    protected $extJsConfigurationClass = '';

    protected $values = [];

    protected $executorConfig = [];

    protected $actions = [];

    protected $isShellCommand = false;

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
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
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
    public function getExtJsConfigurationClass()
    {
        return $this->extJsConfigurationClass;
    }

    /**
     * @param string $extJsConfigurationClass
     * @return $this
     */
    public function setExtJsConfigurationClass($extJsConfigurationClass)
    {
        $this->extJsConfigurationClass = $extJsConfigurationClass;
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

    /**
     * @return array
     */
    public function getExecutorConfig()
    {
        return $this->executorConfig;
    }

    /**
     * @param array $executorConfig
     * @return $this
     */
    public function setExecutorConfig($executorConfig)
    {
        $this->executorConfig = $executorConfig;
        return $this;
    }

    public function getExtJsSettings(){
        $data = [];
        $data['values'] = $this->getValues();
        $data['executorConfig'] = $this->getExecutorConfig();
        $data['actions'] = $this->getActions();

        foreach($data['actions'] as $i => $actionData){
            $className = $actionData['class'];
            $x = new $className();
            $data['actions'][$i]['extJsClass'] = $x->getExtJsClass();
            $data['actions'][$i]['config'] = $x->getConfig();
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


}
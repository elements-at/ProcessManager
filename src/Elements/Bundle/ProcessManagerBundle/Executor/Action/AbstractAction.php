<?php

namespace Elements\Bundle\ProcessManagerBundle\Executor\Action;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

abstract class AbstractAction
{

    public $extJsClass = '';

    public $name = '';

    protected $config = [];

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
        $this->extJsClass = $extJsClass;

        return $this;
    }


    /**
     * @return string
     */
    public function getName()
    {
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
     * @param $monitoringItem MonitoringItem
     * @param $actionData
     * @return string
     */
    abstract public function getGridActionHtml($monitoringItem, $actionData);


    /**
     * Perfoms the action
     *
     * @param $monitoringItem MonitoringItem
     * @param $actionData array
     * @return mixed
     */
    abstract public function execute($monitoringItem, $actionData);

    /**
     * @param $monitoringItem MonitoringItem
     * @param $actionData array
     */
    public function preMonitoringItemDeletion($monitoringItem, $actionData)
    {
    }

}

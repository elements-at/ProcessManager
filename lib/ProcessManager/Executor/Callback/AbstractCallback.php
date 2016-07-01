<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 24.06.2016
 * Time: 13:35
 */
namespace ProcessManager\Executor\Callback;

abstract class AbstractCallback
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
     */
    public function setExtJsClass($extJsClass)
    {
        $this->extJsClass = $extJsClass;
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
     */
    public function setName($name)
    {
        $this->name = $name;
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

}
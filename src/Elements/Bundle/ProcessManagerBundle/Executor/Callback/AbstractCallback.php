<?php

namespace Elements\Bundle\ProcessManagerBundle\Executor\Callback;

abstract class AbstractCallback
{

    public $extJsClass = '';

    public $name = '';

    protected $config = [];

    /**
     * AbstractCallback constructor.
     * @param null | array $config
     */
    public function __construct($config)
    {
        if(is_array($config)){
            foreach($config as $key => $value){
                $setter = "set".ucfirst($key);
                if(method_exists($this,$setter)){
                    $this->$setter($value);
                }
            }
        }
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
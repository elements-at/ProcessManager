<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor\Callback;

abstract class AbstractCallback
{
    public string $extJsClass = '';

    public string $name = '';

    protected string $jsFile ='';

    /**
     * @var array<mixed>
     */
    protected array $config = [];

    /**
     * AbstractCallback constructor.
     *
     * @param string $name
     * @param string $extJsClass
     * @param string $jsFile
     * @param array<mixed> $config
     */
    public function __construct(string $name, string $extJsClass, string $jsFile = '', array $config = [])
    {
        $this->setName($name);
        $this->setExtJsClass($extJsClass);
        $this->setJsFile($jsFile);

        if (is_array($config)) {
            foreach ($config as $key => $value) {
                $setter = 'set'.ucfirst($key);
                if (method_exists($this, $setter)) {
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
    public function setExtJsClass(string $extJsClass): void
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
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array<mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array<mixed> $config
     *
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return string
     */
    public function getJsFile(): string
    {
        return $this->jsFile;
    }

    /**
     * @param string $jsFile
     *
     * @return $this
     */
    public function setJsFile(string $jsFile)
    {
        $this->jsFile = $jsFile;

        return $this;
    }
}

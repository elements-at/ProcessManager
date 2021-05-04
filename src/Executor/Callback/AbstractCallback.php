<?php

/**
 * Elements.at
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) elements.at New Media Solutions GmbH (https://www.elements.at)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor\Callback;

abstract class AbstractCallback
{
    public $extJsClass = '';

    public $name = '';

    protected string $jsFile ="";
    protected $config = [];

    /**
     * AbstractCallback constructor.
     *
     */
    public function __construct(string $name, string $extJsClass, string $jsFile = "", array $config = [])
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
     *
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
    public function getJsFile(): string
    {
        return $this->jsFile;
    }

    /**
     * @param string $jsFile
     * @return $this
     */
    public function setJsFile($jsFile)
    {
        $this->jsFile = $jsFile;
        return $this;
    }


}

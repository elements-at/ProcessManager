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

namespace Elements\Bundle\ProcessManagerBundle\Executor\Logger;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Monolog\Handler\StreamHandler;

abstract class AbstractLogger
{
    final public const LOG_FORMAT_SIMPLE = "[%datetime%] %channel%.%level_name%: %message% \n";

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
     *
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
     *
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
     *
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
     *
     * @return string
     */
    abstract public function getGridLoggerHtml($monitoringItem, $actionData);

    /**
     * @param array $config
     * @param MonitoringItem $monitoringItem
     *
     * @return StreamHandler
     */
    abstract public function createStreamHandler($config, $monitoringItem);
}

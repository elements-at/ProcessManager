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

namespace Elements\Bundle\ProcessManagerBundle\Executor\Action;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

abstract class AbstractAction
{
    use \Pimcore\Model\DataObject\Traits\ObjectVarTrait;

    public $extJsClass = '';

    public $name = '';

    protected $config = [];

    /**
     * @param $key
     * @return string
     */
    protected function trans($key){
        $translator = \Pimcore::getKernel()->getContainer()->get('translator');
        return $translator->trans($key,[],'admin');
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
    abstract public function getGridActionHtml($monitoringItem, $actionData);

    /**
     * Perfoms the action
     *
     * @param $monitoringItem MonitoringItem
     * @param $actionData array
     *
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

    /**
     * returns data which can be used in action classes
     * @param MonitoringItem $monitoringItem
     * @param array $actionData
     * @return array
     */
    public function toJson(MonitoringItem $monitoringItem,$actionData){
        $data = $this->getObjectVars();
        return $data;
    }
}

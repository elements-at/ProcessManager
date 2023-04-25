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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class OpenItem extends AbstractAction
{
    public $name = 'openItem';
    public $extJsClass = 'pimcore.plugin.processmanager.executor.action.openItem';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var int
     */
    protected $itemId = null;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getItemId(): int
    {
        return $this->itemId;
    }

    /**
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
        return $this;
    }


    /**
     * @param $monitoringItem MonitoringItem
     * @param $actionData
     *
     * @return object | null
     */
    protected function getItem($monitoringItem, $actionData){
        return \Pimcore\Model\Element\Service::getElementById($actionData['type'],$actionData['itemId']);
    }

    protected function getIcon($type){
        $icons = [
            'document' => "/bundles/pimcoreadmin/img/flat-white-icons/page.svg",
            'object' => "/bundles/pimcoreadmin/img/flat-white-icons/object.svg",
            'asset' =>  "/bundles/pimcoreadmin/img/flat-white-icons/camera.svg"
        ];
        return $icons[$type];
    }
    /**
     * @param $monitoringItem MonitoringItem
     * @param $actionData
     *
     * @return string
     */
    public function getGridActionHtml($monitoringItem, $actionData)
    {
        if (in_array($monitoringItem->getStatus(), $actionData['executeAtStates'])) {

            $item = $this->getItem($monitoringItem,$actionData);
            if ($item) {
                $icon = $this->getIcon($actionData['type']);
                $type = $item->getType();
                $method = 'pimcore.helpers.open'.ucfirst($actionData['type']);
                $cssClass = 'process_manager_icon_action_open '.$actionData['type'].' ';
                $s =  '<a href="#" onClick="'.$method.'('.$actionData['itemId'].',\''.$item->getType().'\');" class="'.$cssClass.' " alt="'.$this->trans('open').'" title="'.$this->trans('open').'">&nbsp;</a>&nbsp;';

                return $s;
            } else {
                return $this->trans('plugin_pm_item_doesnt_exist');
            }
        }
    }


    public function toJson(MonitoringItem $monitoringItem, $actionData)
    {
        $data = parent::toJson($monitoringItem, $actionData);
        $data['item_exists'] = false;
        $data['item_type'] = null;

        if (in_array($monitoringItem->getStatus(), $actionData['executeAtStates'])) {
            $item = $this->getItem($monitoringItem,$actionData);
            if($item) {
                $data['item_exists'] = true;
                $data['item_type'] = $item->getType();
            }else{
                $data['item_exists'] = false;
            }
        }
        return $data;
    }

    public function execute($monitoringItem, $actionData)
    {
    }

    /**
     * @inheritDoc
     */
    public function getStorageData(): array
    {
        return [
            'label' => $this->getLabel(),
            'type' => $this->getType(),
            'itemId' => $this->getItemId(),
            'executeAtStates' => $this->getExecuteAtStates(),
            'class' => self::class
        ];
    }
}

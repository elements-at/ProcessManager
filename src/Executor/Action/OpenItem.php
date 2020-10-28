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
     * @param $monitoringItem MonitoringItem
     * @param $actionData
     *
     * @return object | null
     */
    protected function getItem($monitoringItem, $actionData){
        return \Pimcore\Model\Element\Service::getElementById($actionData['type'],$actionData['item_id']);
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
        if ($monitoringItem->getStatus() == $monitoringItem::STATUS_FINISHED) {

            $item = $this->getItem($monitoringItem,$actionData);
            if ($item) {
                $icon = $this->getIcon($actionData['type']);
                $type = $item->getType();
                $method = 'pimcore.helpers.open'.ucfirst($actionData['type']);
                $cssClass = 'process_manager_icon_action_open '.$actionData['type'].' ';
                $s =  '<a href="#" onClick="'.$method.'('.$actionData['item_id'].',\''.$item->getType().'\');" class="'.$cssClass.' " alt="'.$this->trans('open').'" title="'.$this->trans('open').'">&nbsp;</a>&nbsp;';

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

        if ($monitoringItem->getStatus() == $monitoringItem::STATUS_FINISHED) {
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
}

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

class JsEvent extends AbstractAction
{
    public $name = 'jsEvent';
    public $extJsClass = 'pimcore.plugin.processmanager.executor.action.jsEvent';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var string
     */
    protected $eventName = '';

    /**
     * @var string
     */
    protected $icon = '';

    /**
     * @var string
     */
    protected $eventData = '';

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
    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @param string $eventName
     * @return $this
     */
    public function setEventName($eventName)
    {
        $this->eventName = $eventName;
        return $this;
    }

    /**
     * @return string
     */
    public function getEventData(): string
    {
        return $this->eventData;
    }

    /**
     * @param string $eventData
     * @return $this
     */
    public function setEventData($eventData)
    {
        $this->eventData = $eventData;
        return $this;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }


    public function getGridActionHtml($monitoringItem, $actionData)
    {
        $js = "var event = new CustomEvent('%s', {detail: %s});
                    console.log('Dispatch event', event);
                    document.dispatchEvent(event);";

        $js = sprintf($js, $actionData['eventName'], htmlspecialchars(json_encode($actionData)));

        $data = [
            'actionData' => $actionData,
            'monitoringItem' => $monitoringItem
        ];
        $js = 'processmanagerPluginJsEvent.executeActionForGridList('.htmlspecialchars(json_encode($data)).')';
        $img = '<img src="'.($actionData['icon'] ?: '/bundles/pimcoreadmin/img/flat-color-icons/biohazard.svg').'" />';
        $link = '<a href="javascript://" onClick="'.$js
            .'" class="process_manager_icon_download process_manager_action_js_event"'
            .'alt="'.$actionData['label'].'">'.$img.'</a>';
        return $link;

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
            'icon' => $this->getIcon(),
            'eventName' => $this->getEventName(),
            'eventData' => $this->getEventData(),
            'class' => self::class
        ];
    }
}

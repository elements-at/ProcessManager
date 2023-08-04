<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor\Action;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

class JsEvent extends AbstractAction
{
    public string $name = 'jsEvent';

    public string $extJsClass = 'pimcore.plugin.processmanager.executor.action.jsEvent';

    protected string $label = '';

    protected string $eventName = '';

    protected string $icon = '';

    protected string $eventData = '';

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel(string $label)
    {
        $this->label = $label;

        return $this;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @param string $eventName
     *
     * @return $this
     */
    public function setEventName(string $eventName)
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function getEventData(): string
    {
        return $this->eventData;
    }

    /**
     * @param string $eventData
     *
     * @return $this
     */
    public function setEventData(string $eventData)
    {
        $this->eventData = $eventData;

        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     *
     * @return $this
     */
    public function setIcon(string $icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @param MonitoringItem $monitoringItem
     * @param array<mixed> $actionData
     *
     * @return string
     *
     * @throws \JsonException
     */
    public function getGridActionHtml(MonitoringItem $monitoringItem, array $actionData): string
    {
        $js = "var event = new CustomEvent('%s', {detail: %s});
                    console.log('Dispatch event', event);
                    document.dispatchEvent(event);";

        $js = sprintf($js, $actionData['eventName'], htmlspecialchars(json_encode($actionData, JSON_THROW_ON_ERROR)));

        $data = [
            'actionData' => $actionData,
            'monitoringItem' => $monitoringItem,
        ];
        $js = 'processmanagerPluginJsEvent.executeActionForGridList('.htmlspecialchars(json_encode($data, JSON_THROW_ON_ERROR)).')';
        $img = '<img src="'.($actionData['icon'] ?: '/bundles/pimcoreadmin/img/flat-color-icons/biohazard.svg').'" />';

        return '<a href="javascript://" onClick="'.$js
            .'" class="process_manager_icon_download process_manager_action_js_event"'
            .'alt="'.$actionData['label'].'">'.$img.'</a>';

    }

    /**
     * @param $monitoringItem MonitoringItem
     * @param array<mixed> $actionData
     *
     * @return void
     */
    public function execute(MonitoringItem $monitoringItem, array $actionData): void
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function getStorageData(): array
    {

        return [
            'label' => $this->getLabel(),
            'icon' => $this->getIcon(),
            'eventName' => $this->getEventName(),
            'eventData' => $this->getEventData(),
            'class' => self::class,
        ];
    }
}

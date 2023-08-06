<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor\Action;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

class OpenItem extends AbstractAction
{
    public string $name = 'openItem';

    public string $extJsClass = 'pimcore.plugin.processmanager.executor.action.openItem';

    protected string $label = '';

    protected string $type = '';

    /**
     * @var int
     */
    protected $itemId = null;

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

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    /**
     * @param int $itemId
     *
     * @return $this
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * @param $monitoringItem MonitoringItem
     * @param array<mixed> $actionData
     *
     * @return object|null
     */
    protected function getItem(MonitoringItem $monitoringItem, array $actionData): ?object
    {
        return \Pimcore\Model\Element\Service::getElementById($actionData['type'], $actionData['itemId']);
    }

    /**
     * @param $type string
     *
     * @return string
     */
    protected function getIcon(string $type): string
    {
        $icons = [
            'document' => '/bundles/pimcoreadmin/img/flat-white-icons/page.svg',
            'object' => '/bundles/pimcoreadmin/img/flat-white-icons/object.svg',
            'asset' =>  '/bundles/pimcoreadmin/img/flat-white-icons/camera.svg',
        ];

        return $icons[$type];
    }

    /**
     * @param $monitoringItem MonitoringItem
     * @param array<mixed> $actionData
     *
     * @return string
     */
    public function getGridActionHtml(MonitoringItem $monitoringItem, array $actionData): string
    {
        if (in_array($monitoringItem->getStatus(), $actionData['executeAtStates'])) {

            $item = $this->getItem($monitoringItem, $actionData);
            if ($item) {
                $icon = $this->getIcon($actionData['type']);
                $type = $item->getType();
                $method = 'pimcore.helpers.open'.ucfirst((string) $actionData['type']);
                $cssClass = 'process_manager_icon_action_open '.$actionData['type'].' ';

                return '<a href="#"
                            data-process-manager-trigger="openItem"
                            data-process-manager-id="' . $monitoringItem->getId() . '"
                            data-process-manager-item-id="' . $actionData['itemId'] . '"
                            data-process-manager-item-action-type="' . ucfirst((string) $actionData['type']) . '"
                            data-process-manager-item-type="' . $item->getType() . '"


                class="'.$cssClass.' " alt="'.$this->trans('open').'" title="'.$this->trans('open').'">&nbsp;</a>&nbsp;';
            } else {
                return $this->trans('plugin_pm_item_doesnt_exist');
            }
        }

        return '';
    }

    /**
     * @param MonitoringItem $monitoringItem
     * @param $actionData array<mixed>
     *
     * @return array<string,mixed>
     */
    public function toJson(MonitoringItem $monitoringItem, array $actionData): array
    {
        $data = parent::toJson($monitoringItem, $actionData);
        $data['item_exists'] = false;
        $data['item_type'] = null;

        if (in_array($monitoringItem->getStatus(), $actionData['executeAtStates'])) {
            $item = $this->getItem($monitoringItem, $actionData);
            if($item) {
                $data['item_exists'] = true;
                $data['item_type'] = $item->getType();
            } else {
                $data['item_exists'] = false;
            }
        }

        return $data;
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
            'type' => $this->getType(),
            'itemId' => $this->getItemId(),
            'executeAtStates' => $this->getExecuteAtStates(),
            'class' => self::class,
        ];
    }
}

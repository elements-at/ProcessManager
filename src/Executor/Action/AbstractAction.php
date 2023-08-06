<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor\Action;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

abstract class AbstractAction
{
    use \Pimcore\Model\DataObject\Traits\ObjectVarTrait;

    public string $extJsClass = '';

    public string $name = '';

    /**
     * @var array<string>
     */
    public array $executeAtStates = ['finished'];

    /**
     * @var array<mixed>
     */
    protected array $config = [];

    /**
     * @param $key string
     *
     * @return string
     */
    protected function trans(string $key): string
    {
        $translator = \Pimcore::getKernel()->getContainer()->get('translator');

        return $translator->trans($key, [], 'admin');
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
    public function setExtJsClass(string $extJsClass)
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
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getExecuteAtStates(): array
    {
        return $this->executeAtStates;
    }

    /**
     * @param array<mixed> $executeAtStates
     *
     * @return $this
     */
    public function setExecuteAtStates(array $executeAtStates)
    {
        $this->executeAtStates = $executeAtStates;

        return $this;
    }

    /**
     * @param array<mixed> $data
     *
     * @return void
     */
    public function setValues(array $data): void
    {
        $data = $this->prepareDataForSetValues($data);
        foreach($data as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if(method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    protected function prepareDataForSetValues(array $data): array{
        return $data;
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
     * @param $monitoringItem MonitoringItem
     * @param array<mixed> $actionData
     *
     * @return string
     */
    abstract public function getGridActionHtml(MonitoringItem $monitoringItem, array $actionData): string;

    /**
     * Performs the action
     *
     * @param $monitoringItem MonitoringItem
     * @param array<mixed> $actionData
     *
     * @return mixed
     */
    abstract public function execute(MonitoringItem $monitoringItem, array $actionData);

    /**
     * @param $monitoringItem MonitoringItem
     * @param array<mixed> $actionData
     */
    public function preMonitoringItemDeletion(MonitoringItem $monitoringItem, array $actionData): void
    {
    }

    /**
     * returns data which can be used in action classes
     *
     * @param MonitoringItem $monitoringItem
     * @param array<mixed> $actionData
     *
     * @return array<string,mixed>
     */
    public function toJson(MonitoringItem $monitoringItem, array $actionData): array
    {
        return $this->getObjectVars();
    }

    /**
     * returns an array for storage in the database
     *
     * @return array<mixed>
     */
    abstract public function getStorageData(): array;
}

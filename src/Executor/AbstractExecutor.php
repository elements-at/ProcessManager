<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor;

use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Tool\Console;

abstract class AbstractExecutor implements \JsonSerializable
{
    protected string $name;

    protected string $extJsClass;

    protected ?Configuration $config = null;

    /**
     * @var array<mixed>
     */
    protected array $values = [];

    /**
     * @var array<mixed>
     */
    protected array $loggers = [];

    /**
     * @var array<mixed>
     */
    protected array $executorConfig = [];

    /**
     * @var array<mixed>
     */
    protected array $actions = [];

    protected bool $isShellCommand = false;

    public function __construct()
    {
    }

    public function getIsShellCommand(): bool
    {
        return $this->isShellCommand;
    }

    public function setIsShellCommand(bool $isShellCommand): self
    {
        $this->isShellCommand = $isShellCommand;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if ($this->name === '' || $this->name === '0') {
            $array = explode('\\', static::class);
            $this->name = lcfirst(array_pop($array));
        }

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

    public function getConfig(): ?Configuration
    {
        return $this->config;
    }

    public function setConfig(Configuration $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getExtJsClass(): string
    {
        return $this->extJsClass;
    }

    public function setExtJsClass(string $extJsClass): self
    {
        $this->extJsClass = $extJsClass;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array<mixed> $values
     *
     * @return $this
     */
    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getExtJsSettings(): array
    {
        $data = [];
        $executorConfig = [
            'extJsClass' => $this->getExtJsClass(),
            'name' => $this->getName(),
            'class' => $this->getConfig()->getExecutorClass(),
        ];
        $data['executorConfig'] = $executorConfig;

        $data['values'] = $this->getValues();
        $data['values']['id'] = $this->getConfig()->getId();

        $data['loggers'] = $this->getLoggers();
        $data['actions'] = $this->getActions();

        foreach ((array)$data['actions'] as $i => $actionData) {
            $className = $actionData['class'];
            $x = new $className();
            $data['actions'][$i]['extJsClass'] = $x->getExtJsClass();
            $data['actions'][$i]['config'] = $x->getConfig();
        }

        foreach ((array)$data['loggers'] as $i => $loggerData) {
            $className = $loggerData['class'];
            $x = new $className();
            $data['loggers'][$i]['extJsClass'] = $x->getExtJsClass();
            $data['loggers'][$i]['config'] = $x->getConfig();
        }

        return $data;
    }

    /**
     * @return array<mixed>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param array<mixed> $actions
     *
     * @return $this
     */
    public function setActions(array $actions): self
    {
        $this->actions = $actions;

        return $this;
    }

    public function getShellCommand(MonitoringItem $monitoringItem): string
    {
        return Console::getPhpCli() . ' ' . realpath(PIMCORE_PROJECT_ROOT . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'console') . ' process-manager:execute-shell-cmd --monitoring-item-id=' . $monitoringItem->getId();
    }

    /**
     * returns the command which should be executed
     *
     * the CallbackSettings are only passed at execution time
     *
     * @param string[] $callbackSettings
     * @param null | MonitoringItem $monitoringItem
     *
     * @return mixed
     */
    abstract public function getCommand($callbackSettings = [], $monitoringItem = null);

    public function jsonSerialize(): mixed
    {
        return ['class' => static::class, ...get_object_vars($this)];
    }

    /**
     * @return array<mixed>
     */
    public function getLoggers()
    {
        return $this->loggers;
    }

    /**
     * @param array<mixed> $loggers
     *
     * @return $this
     */
    public function setLoggers(array $loggers)
    {
        $this->loggers = $loggers;

        return $this;
    }

    public function getStorageValue(): string
    {
        $actions = (array)$this->getActions();
        foreach ($actions as $i => $data) {
            if (is_object($data) && method_exists($data, 'getStorageData')) {
                $actions[$i] = $data->getStorageData();
            }
        }
        $data = [
            'values' => (array)$this->getValues(),
            'actions' => $actions,
            'loggers' => (array)$this->getLoggers(),
        ];

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<mixed> $values
     *
     * @return $this
     */
    protected function setData(array $values)
    {
        foreach ($values as $key => $value) {
            $setter = 'set' . ucfirst((string)$key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }

        return $this;
    }

    /**
     * @return Configuration
     */
    public function setDataFromResource(Configuration $configuration): Configuration
    {
        $settings = $configuration->getExecutorSettings();
        if (is_string($settings)) {
            $this->setData(json_decode($settings, true, 512, JSON_THROW_ON_ERROR));
        }
        $this->setConfig($configuration);

        return $configuration;
    }
}

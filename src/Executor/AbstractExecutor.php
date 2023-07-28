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

namespace Elements\Bundle\ProcessManagerBundle\Executor;

use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Tool\Console;

abstract class AbstractExecutor implements \JsonSerializable
{
    protected string $name = '';

    protected string $extJsClass = '';

    protected array $values = [];

    protected array $loggers = [];

    protected array $executorConfig = [];

    protected array $actions = [];

    protected bool $isShellCommand = false;

    /**
     * @param Configuration $config
     */
    public function __construct(protected array $config = [])
    {
    }

    /**
     * @return bool
     */
    public function getIsShellCommand()
    {
        return $this->isShellCommand;
    }

    /**
     * @param bool $isShellCommand
     *
     * @return $this
     */
    public function setIsShellCommand($isShellCommand)
    {
        $this->isShellCommand = $isShellCommand;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (!$this->name) {
            $this->name = lcfirst(array_pop(explode('\\', static::class)));
        }

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
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Configuration $config
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
    public function getExtJsClass()
    {
        return $this->extJsClass;
    }

    public function setExtJsClass(string $extJsClass): self
    {
        $this->extJsClass = $extJsClass;

        return $this;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array $values
     *
     * @return $this
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

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
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param array $actions
     *
     * @return $this
     */
    public function setActions($actions)
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     *
     * Tests
     *
     * @param MonitoringItem $monitoringItem
     *
     * @return string
     *
     */
    public function getShellCommand(MonitoringItem $monitoringItem)
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
        $values = ['class' => static::class, ...get_object_vars($this)];

        return $values;
    }

    /**
     * @return array
     */
    public function getLoggers()
    {
        return $this->loggers;
    }

    /**
     * @param array $loggers
     *
     * @return $this
     */
    public function setLoggers($loggers)
    {
        $this->loggers = $loggers;

        return $this;
    }

    public function getStorageValue(): string
    {
        $actions = (array)$this->getActions();
        foreach($actions as $i => $data) {
            if(is_object($data) && method_exists($data, 'getStorageData')) {
                $actions[$i] = $data->getStorageData();
            }
        }
        $data = [
            'values' => (array)$this->getValues(),
            'actions' => $actions,
            'loggers' => (array)$this->getLoggers()
        ];

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    protected function setData($values)
    {
        foreach ($values as $key => $value) {
            $setter = 'set' . ucfirst((string) $key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }

        return $this;
    }

    /**
     * @return Configuration
     */
    public function setDataFromResource(Configuration $configuration)
    {
        $settings = $configuration->getExecutorSettings();
        if (is_string($settings)) {
            $this->setData(json_decode($settings, true, 512, JSON_THROW_ON_ERROR));
        }
        $this->setConfig($configuration);

        return $configuration;
    }
}

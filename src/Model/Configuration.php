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

namespace Elements\Bundle\ProcessManagerBundle\Model;

use Elements\Bundle\ProcessManagerBundle\Executor\AbstractExecutor;
use Pimcore\Logger;
use Pimcore\Tool;

/**
 * @method  Configuration save($params = []) Configuration
 * @method  Configuration delete() void
 */
class Configuration extends \Pimcore\Model\AbstractModel
{
    public $id;

    public $name;

    public $group;

    public $description;

    public $creationDate;

    public $modificationDate;

    public $executorClass;

    public $executorSettings;

    public $cronJob;

    public $lastCronJobExecution;

    public $active;

    public $keepVersions;

    public $restrictToRoles;

    public $restrictToPermissions;

    protected $executorClassObject;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId(mixed $id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(mixed $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription(mixed $description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate(mixed $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return mixed
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    public function setModificationDate(mixed $modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * @param $id
     *
     * @return Configuration
     */
    public static function getById($id)
    {
        $self = new self();
        $self->getDao()->getById($id);
        if ($self->getId()) {
            return $self;
        }
    }

    /**
     * @return mixed
     */
    public function getExecutorSettings()
    {
        return $this->executorSettings;
    }

    /**
     * @return $this
     */
    public function setExecutorSettings(mixed $executorSettings)
    {
        $this->executorSettings = $executorSettings;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExecutorClass()
    {
        return $this->executorClass;
    }

    /**
     * @return $this
     */
    public function setExecutorClass(mixed $executorClass)
    {
        $this->executorClass = $executorClass;

        return $this;
    }

    /**
     * @return AbstractExecutor
     */
    public function getExecutorClassObject()
    {
        if (!$this->executorClassObject) {

            /**
             * @var AbstractExecutor $class
             */
            $className = $this->getExecutorClass();
            $class = new $className();
            $class->setDataFromResource($this);
            $this->executorClassObject = $class;
        }

        return $this->executorClassObject;
    }

    public function getCommand()
    {
        $executorClass = $this->getExecutorClassObject();
        if ($executorClass) {
            return $executorClass->getCommand();
        }
    }

    /**
     * @return MonitoringItem[]
     */
    public function getRunningProcesses()
    {
        $list = new MonitoringItem\Listing();
        $list->setCondition(
            'configurationId = ? AND pid > 0 AND status != ? ',
            [$this->getId(), MonitoringItem::STATUS_FAILED]
        );
        $items = [];
        foreach ($list->load() as $item) {
            if ($item->isAlive()) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     *
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCronJob()
    {
        return $this->cronJob;
    }

    /**
     * @return $this
     */
    public function setCronJob(mixed $cronJob)
    {
        if ($cronJob && !\Cron\CronExpression::isValidExpression($cronJob)) {
            throw new \Exception('The cronjob expression "' . $cronJob.'" is not valid. Please provide a valid Cronjob expression');
        }

        $this->cronJob = $cronJob;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastCronJobExecution()
    {
        return $this->lastCronJobExecution;
    }

    /**
     * @return $this
     */
    public function setLastCronJobExecution(mixed $lastCronJobExecution)
    {
        $this->lastCronJobExecution = $lastCronJobExecution;

        return $this;
    }

    public function getNextCronJobExecutionTimestamp()
    {
        if ($this->getCronJob()) {
            if (Tool::classExists('\\' . \Cron\CronExpression::class)) {
                $cron = new \Cron\CronExpression($this->getCronJob());
                $lastExecution = $this->getLastCronJobExecution();
                if (!$lastExecution) {
                    $lastExecution = $this->getModificationDate();
                }
                $lastRunDate = new \DateTime(date('Y-m-d H:i', $lastExecution));
                $nextRun = $cron->getNextRunDate($lastRunDate);
                $nextRunTs = $nextRun->getTimestamp();

                return $nextRunTs;
            } else {
                Logger::error('Class \Cron\CronExpression does not exist');
            }
        }
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return (bool)$this->active;
    }

    /**
     * @return $this
     */
    public function setActive(mixed $active)
    {
        $this->active = (bool)$active;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getKeepVersions()
    {
        return $this->keepVersions;
    }

    /**
     * @return $this
     */
    public function setKeepVersions(mixed $keepVersions)
    {
        $this->keepVersions = $keepVersions;

        return $this;
    }

    /**
     * @param $method
     * @param $arguments
     *
     * @return Configuration|Configuration[]
     *
     * @throws \Exception
     */
    public static function __callStatic($method, $arguments)
    {

        // check for custom static getters like Object::getByMyfield()
        $propertyName = lcfirst(preg_replace('/^getBy/i', '', (string) $method));
        $list = new Configuration\Listing();
        $list->setCondition($propertyName.' = ?', [$arguments[0]]);

        $limit = $arguments[1]['limit'] ?? false;
        if ($limit) {
            $list->setLimit($limit);
        }
        $result = $list->load();

        if ($limit == 1 && count($result) == 1) {
            return $result[0];
        } else {
            return $result;
        }
    }

    /**
     * @return mixed
     */
    public function getRestrictToRoles()
    {
        return $this->restrictToRoles;
    }

    /**
     * @return $this
     */
    public function setRestrictToRoles(mixed $restrictToRoles)
    {

        $this->restrictToRoles = $this->implodeAsString($restrictToRoles);

        return $this;
    }

    protected function implodeAsString($value): string
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        if (is_string($value) && $value != '' && $value[0] != ',') {
            $value = ','.$value;
        }
        if (!str_ends_with((string) $value, ',') && $value != '') {
            $value .= ',';
        }

        return $value;
    }

    public function setRestrictToPermissions(array | string $restrictToPermissions): self
    {
        $this->restrictToPermissions = $this->implodeAsString($restrictToPermissions);

        return $this;
    }

    public function getRestrictToPermissions(): string
    {
        return $this->restrictToPermissions;
    }
}

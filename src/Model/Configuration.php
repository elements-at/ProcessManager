<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Model;

use Elements\Bundle\ProcessManagerBundle\Executor\AbstractExecutor;
use Pimcore\Logger;
use Pimcore\Tool;

/**
 * @method  Configuration save($params = []) Configuration
 * @method  Configuration delete() void
 * @method Configuration|null getDao()
 */
class Configuration extends \Pimcore\Model\AbstractModel
{
    public ?string $id;

    public string $name;

    public string $group;

    public string $description;

    public mixed $creationDate;

    public mixed $modificationDate;

    public mixed $executorClass;

    public string $executorSettings;

    public string $cronJob;

    public mixed $lastCronJobExecution;

    public bool $active;

    public mixed $keepVersions;

    public mixed $restrictToRoles;

    public ?string $restrictToPermissions = null;

    protected AbstractExecutor $executorClassObject;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id ?? null;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(mixed $description): void
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

    public function setCreationDate(mixed $creationDate): void
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

    public function setModificationDate(mixed $modificationDate): void
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * @param string $id
     *
     * @return Configuration|null
     */
    public static function getById(string $id): ?Configuration
    {
        $self = new self();
        $self->getDao()->getById($id);
        if (!is_null($self->getId())) {
            return $self;
        }

        return null;
    }

    public function getExecutorSettings(): string
    {
        return $this->executorSettings;
    }

    /**
     * @return $this
     */
    public function setExecutorSettings(string $executorSettings)
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

    public function getExecutorClassObject(): AbstractExecutor
    {
        if (!isset($this->executorClassObject)) {

            $className = $this->getExecutorClass();
            $class = new $className();
            $class->setDataFromResource($this);
            $this->executorClassObject = $class;
        }

        return $this->executorClassObject;
    }

    /**
     * @return mixed|void
     *
     * @throws \JsonException
     */
    public function getCommand()
    {
        $executorClass = $this->getExecutorClassObject();

        return $executorClass->getCommand();
    }

    /**
     * @return MonitoringItem[]
     */
    public function getRunningProcesses(): array
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

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getCronJob(): string
    {
        return $this->cronJob;
    }

    public function setCronJob(string $cronJob): self
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
     * @return mixed $this
     */
    public function setLastCronJobExecution(mixed $lastCronJobExecution)
    {
        $this->lastCronJobExecution = $lastCronJobExecution;

        return $this;
    }

    /**
     * @return int|void
     *
     * @throws \Exception
     */
    public function getNextCronJobExecutionTimestamp()
    {
        if ($this->getCronJob() !== '' && $this->getCronJob() !== '0') {
            if (Tool::classExists('\\' . \Cron\CronExpression::class)) {
                $cron = new \Cron\CronExpression($this->getCronJob());
                $lastExecution = $this->getLastCronJobExecution();
                if (!$lastExecution) {
                    $lastExecution = $this->getModificationDate();
                }
                $lastRunDate = new \DateTime(date('Y-m-d H:i', $lastExecution));
                $nextRun = $cron->getNextRunDate($lastRunDate);

                return $nextRun->getTimestamp();
            } else {
                Logger::error('Class \Cron\CronExpression does not exist');
            }
        }
    }

    /**
     * @return bool
     */
    public function getActive(): bool
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
     * @param mixed $method
     * @param array<mixed> $arguments
     *
     * @return Configuration|Configuration[]
     *
     * @throws \Exception
     */
    public static function __callStatic(mixed $method, array $arguments)
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

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function implodeAsString(mixed $value): string
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

    /**
     * @param array<mixed>|string $restrictToPermissions
     *
     * @return $this
     */
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

<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Model;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Executor\Logger\AbstractLogger;
use Elements\Bundle\ProcessManagerBundle\Message\CheckCommandAliveMessage;
use Elements\Bundle\ProcessManagerBundle\Message\StopProcessMessage;
use Monolog\Logger;
use Symfony\Component\Process\Process;

/**
 * Class MonitoringItem
 *
 * @method  MonitoringItem save() MonitoringItem
 * @method  MonitoringItem getDao() MonitoringItem\Dao
 * @method  MonitoringItem delete() void
 * @method  MonitoringItem load() []MonitoringItem
 */
class MonitoringItem extends \Pimcore\Model\AbstractModel
{
    final public const STATUS_UNKNOWN = 'unknown';

    final public const STATUS_FINISHED = 'finished';

    final public const STATUS_FINISHED_WITH_ERROR = 'finished_with_errors';

    final public const STATUS_FAILED = 'failed';

    final public const STATUS_RUNNING = 'running';

    final public const STATUS_INITIALIZING = 'initializing';

    /**
     * @var int
     */
    public int $id;

    /**
     * @var int|null
     */
    public int|null $parentId;

    public string $name = '';

    public string $message = '';

    public string $status = self::STATUS_UNKNOWN;

    public string $command = '';

    public int $creationDate;

    public string $configurationId = '';

    public ?int $reportedDate = null;

    public int $modificationDate;

    public int|null $pid = null;

    /**
     * @var array<mixed>
     */
    public array $loggers = [];

    /**
     * @var array<mixed>
     */
    public array $actions = [];

    public int $executedByUser = 0;

    protected \Elements\Bundle\ProcessManagerBundle\Logger $logger;

    /**
     * @var array<mixed>
     */
    public array $callbackSettings = [];

    public int|null $totalWorkload = null;

    public int|null $currentWorkload = 0;

    public int $currentStep = 0;

    public int $totalSteps = 1;

    /**
     * The dummy object won't be saved
     * It is just created when no monitoring item id is passed
     * so we dont have to check if the monitoring item is available
     *
     * @var bool
     */
    public bool $isDummy = false;

    /**
     * @var bool
     */
    public bool $published = true;

    /**
     * @var bool
     */
    public bool $messengerPending = false;

    /**
     * @var string
     */
    public string $group = '';

    /**
     * @var int | null
     */
    public ?int $deleteAfterHours = null;

    /**
     * @var array<mixed>
     */
    public array $metaData = [];

    /**
     * @var bool
     */
    public bool $hasCriticalError = false;

    /**
     * Error Level which sould be considered as critical
     * ['critical','error','emergency']...
     *
     * @var array<mixed>
     */
    public array $criticalErrorLevel = [];

    /**
     * @return array<mixed>
     */
    public function getCriticalErrorLevel(): array
    {
        return $this->criticalErrorLevel;
    }

    /**
     * @param array<mixed> $criticalErrorLevel
     *
     * @return $this
     */
    public function setCriticalErrorLevel(array $criticalErrorLevel)
    {
        $this->criticalErrorLevel = $criticalErrorLevel;

        return $this;
    }

    public function getDeleteAfterHours(): ?int
    {
        return $this->deleteAfterHours;
    }

    public function setDeleteAfterHours(?int $deleteAfterHours): static
    {
        $this->deleteAfterHours = $deleteAfterHours;

        return $this;
    }

    public function getHasCriticalError(): bool
    {
        return $this->hasCriticalError;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function setHasCriticalError(bool $hasCriticalError): self
    {
        $this->hasCriticalError = $hasCriticalError;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }

    /**
     * @param array<mixed> $metaData
     */
    public function setMetaData(array $metaData): self
    {
        $this->metaData = $metaData;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function isMessengerPending(): bool
    {
        return $this->messengerPending;
    }

    public function setMessengerPending(bool $messengerPending): MonitoringItem
    {
        $this->messengerPending = $messengerPending;

        return $this;
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

    public function getIsDummy(): bool
    {
        return $this->isDummy;
    }

    public function setIsDummy(bool $isDummy): self
    {
        $this->isDummy = $isDummy;

        return $this;
    }

    public static function getById(int $id): ? self
    {
        $self = new self();

        return $self->getDao()->getById($id);
    }

    /**
     * @param array<mixed> $data
     * @param bool $ignoreEmptyValues
     *
     * @return $this
     */
    public function setValues($data = [], bool $ignoreEmptyValues = false): static
    {
        if (is_array($data) && $data !== []) {
            foreach ($data as $key => $value) {
                if(in_array($key, ['callbackSettings', 'actions', 'loggers']) && is_string($value)) {
                    $value = json_decode($value, true);
                }
                if ($key == 'message') {
                    $this->setMessage($value, false);
                } else {
                    $this->setValue($key, $value);
                }
            }
        }

        return $this;
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
     */
    public function setActions(array $actions): self
    {
        $this->actions = $actions;

        return $this;
    }

    public function getTotalWorkload(): ?int
    {
        return $this->totalWorkload;
    }

    public function setTotalWorkload(?int $totalWorkload): self
    {
        $this->totalWorkload = $totalWorkload;

        return $this;
    }

    public function getCurrentWorkload(): ?int
    {
        return $this->currentWorkload;
    }

    public function setCurrentWorkload(?int $currentWorkload): self
    {
        $this->currentWorkload = $currentWorkload;

        return $this;
    }

    public function getProcessManagerConfigObject(): ?Configuration
    {
        if ($id = $this->getConfigurationId()) {
            return Configuration::getById($id);
        }

        return null;
    }

    public function getCurrentStep(): int
    {
        return $this->currentStep;
    }

    public function setCurrentStep(int $currentStep): self
    {
        $this->currentStep = $currentStep;

        return $this;
    }

    public function getTotalSteps(): int
    {
        return $this->totalSteps;
    }

    public function setTotalSteps(int $totalSteps): self
    {
        $this->totalSteps = $totalSteps;

        return $this;
    }

    public function setId(int $id): self
    {
        $this->id = (int)$id;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed|int $logLevel
     */
    public function setMessage(string $message, mixed $logLevel = \Monolog\Logger::NOTICE): self
    {
        $this->message = $message;
        if ($logLevel !== false) {
            $this->getLogger()->log($logLevel, $message);
        }

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setCommand(mixed $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    /** Returns true if the item died unexpectetly
     */
    public function isAlive(): bool
    {
        if($this->isMessengerPending()) {
            return true;
        }
        if ($this->getPid()) {
            $messageBus = \Pimcore::getContainer()->get('messenger.bus.pimcore-core');
            $message = new CheckCommandAliveMessage($this->getId());
            $messageBus->dispatch($message);

            return (bool)self::getById($this->getId())->getPid();
        } else {
            return false;
        }
    }

    public function getCreationDate(): int
    {
        return $this->creationDate;
    }

    public function setCreationDate(int $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getModificationDate(): int
    {
        return $this->modificationDate;
    }

    public function setModificationDate(int $modificationDate): static
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * convenience function to set the process message
     */
    public function setDefaultProcessMessage(string $itemType = 'item', int $logLevel = Logger::NOTICE): self
    {
        $currentWorkload = $this->getCurrentWorkload() ?: 1;

        $this->setMessage(
            'Processing '.$itemType.' '.$currentWorkload.' from '.$this->getTotalWorkload(
            ).' ('.($this->getTotalWorkload() - $currentWorkload).' remaining)',
            $logLevel
        );

        return $this;
    }

    public function resetWorkload(): self
    {
        $this->setCurrentWorkload(0);
        $this->setTotalWorkload(0);

        return $this;
    }

    public function setWorkloadCompleted(): self
    {
        $this->setCurrentWorkload($this->getTotalWorkload());

        return $this;
    }

    public function getDuration(): string
    {
        $took = $this->getModificationDate() - $this->getCreationDate();
        if ($took > 0) {
            if ($took < 60) {
                return $took.'s';
            }

            if ($took < 3600) {
                $minutes = floor($took / 60);
                $seconds = $took - ($minutes * 60);

                return $minutes.'m '.$seconds.'s';
            }

            return gmdate('h', $took).'h '.gmdate('i', $took).'m '.gmdate('s', $took).'s';
        }

        return '';
    }

    /**
     * @return string|null
     */
    public function getConfigurationId(): ?string
    {
        return $this->configurationId;
    }

    /**
     * @return $this
     */
    public function setConfigurationId(mixed $configurationId)
    {
        $this->configurationId = $configurationId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPid(): ?int
    {
        return $this->pid;
    }

    /**
     * @return $this
     */
    public function setPid(mixed $pid)
    {
        $this->pid = $pid;

        return $this;
    }

    public function getLogFile(): string
    {
        return ElementsProcessManagerBundle::getLogDir().$this->getId().'.log';
    }

    public function deleteLogFile(): self
    {
        if (is_file($this->getLogFile())) {
            unlink($this->getLogFile());
        }

        return $this;
    }

    public function resetState(): self
    {
        $this->setStatus(self::STATUS_UNKNOWN);
        $this->resetWorkload();
        $this->setCurrentStep(0)->setTotalSteps(0)->setPid(null);
        $this->setMessage('', false);
        $this->setCreationDate(time());
        $this->setModificationDate(time());
        $this->setHasCriticalError(false);

        return $this;
    }

    public function setCompleted(): void
    {
        $this->setWorkloadCompleted();
        $this->setCurrentStep($this->getTotalSteps());
        //do not change the state if set it to failed - otherwise it would appear to be successfully finished
        if ($this->getStatus() != self::STATUS_FAILED) {
            $this->setStatus($this->getHasCriticalError() ? self::STATUS_FINISHED_WITH_ERROR : self::STATUS_FINISHED);
        }
        $this->save();
    }

    /**
     * @return array<mixed>
     */
    public function getCallbackSettings(): array
    {
        return $this->callbackSettings;
    }

    public function getCallbackSettingsForGrid(): string
    {
        $html = '';
        $data = $this->getCallbackSettings();
        if (!empty($data)) {
            $html .= '<table class="process-manager-callback-settings-table"><tr><th>Key</th><th>Value</th></tr>';

            foreach ($data as $key => $value) {
                $html .= '<tr><td>'.$key.'</td><td>';

                if (is_array($value)) {
                    $html .= '<pre>'.print_r($value, true).'</pre>';
                } else {
                    $html .= $value;
                }
                $html .= '</td></tr>';
            }

            $html .= '</table>';
        }

        return $html;
    }

    /**
     * @param array<mixed> $callbackSettings
     */
    public function setCallbackSettings(array $callbackSettings): self
    {
        $this->callbackSettings = $callbackSettings;

        return $this;
    }

    /**
     * Shorthand to get the values
     *
     * @return array<mixed>
     */
    public function getConfigValues(): array
    {
        return $this->getProcessManagerConfigObject()->getExecutorClassObject()->getValues();
    }

    /**
     * @return array<mixed>
     */
    public function getReportValues(): array
    {
        $data = [];
        foreach (['id', 'pid', 'name', 'command', 'creationDate', 'modificationDate', 'callbackSettings'] as $field) {
            $data[$field] = $this->{'get'.ucfirst($field)}();
        }

        return $data;
    }

    public function getReportedDate(): ?int
    {
        return $this->reportedDate;
    }

    public function setReportedDate(?int $reportedDate): self
    {
        $this->reportedDate = $reportedDate;

        return $this;
    }

    public function getLogger(): \Elements\Bundle\ProcessManagerBundle\Logger
    {
        if (!isset($this->logger)) {
            $this->logger = new \Elements\Bundle\ProcessManagerBundle\Logger();
            $this->logger->setComponent((string)$this->getName());
            if ($loggerData = $this->getLoggers()) {
                foreach ($loggerData as $loggerConfig) {
                    /**
                     * @var AbstractLogger $obj
                     */
                    $class = $loggerConfig['class'];

                    if (\Pimcore\Tool::classExists($class)) {
                        $obj = new $class();
                        $streamHandler = $obj->createStreamHandler($loggerConfig, $this);
                        if ($streamHandler) {
                            $this->logger->addWriter($streamHandler);
                        }
                    }
                }
            }
        }

        return $this->logger;
    }

    public function getExecutedByUser(): int
    {
        return (int)$this->executedByUser;
    }

    public function setExecutedByUser(int $executedByUser): static
    {
        $this->executedByUser = (int)$executedByUser;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getLoggers(): array
    {
        return $this->loggers;
    }

    /**
     * @param array<mixed> $loggers
     *
     */
    public function setLoggers(array $loggers): static
    {
        $this->loggers = $loggers;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getForWebserviceExport(): array
    {
        $data = $this->getObjectVars();

        $data['callbackSettings'] = json_decode((string) $data['callbackSettings'], true, 512, JSON_THROW_ON_ERROR);
        $data['actions'] = json_decode((string) $data['actions'], true, 512, JSON_THROW_ON_ERROR);
        $data['loggers'] = json_decode((string) $data['loggers'], true, 512, JSON_THROW_ON_ERROR);
        unset($data['command']);

        return $data;
    }

    public function getProgressPercentage(): ?int
    {
        if ($this->getCurrentWorkload() && $this->getTotalWorkload()) {
            return (int)round($this->getCurrentWorkload() / ($this->getTotalWorkload() / 100));
        }

        return null;
    }

    public function stopProcess(): bool
    {
        $pid = $this->getPid();
        if($pid) {
            $messageBus = \Pimcore::getContainer()->get('messenger.bus.pimcore-core');
            $message = new StopProcessMessage($this->getId());
            $messageBus->dispatch($message);

            return true;
        }

        return false;
    }

    /**
     * @return MonitoringItem[]
     */
    public function getChildProcesses(): array
    {
        $list = new MonitoringItem\Listing();
        $list->setCondition('parentId = ?', [$this->getId()]);
        $list->setOrder('id');

        return $list->load();
    }

    /**
     * @return array<mixed>
     */
    public function getChildProcessesStatus(): array
    {
        $summary = ['active' => 0, 'failed' => 0, 'finished' => 0];
        $details = ['active' => [], 'failed' => [], 'finished' => []];
        $currentWorkload = 0;

        foreach($this->getChildProcesses() as $child) {
            $details[$child->getStatus()][] = ['id' => $child->getId(), 'message' => $child->getMessage(), 'alive' => $child->isAlive()];

            $currentWorkload += $child->getCurrentWorkload();

            if ($child->getStatus() == self::STATUS_FINISHED) {
                $summary['finished']++;
            } elseif ($child->isAlive()) {
                $summary['active']++;
            } else {
                $summary['failed']++;
            }
        }

        return [
            'summary' => $summary,
            'details' => $details,
            'currentWorkload' => $currentWorkload,
        ];
    }
}

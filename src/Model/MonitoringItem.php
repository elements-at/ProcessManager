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

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Executor\Logger\AbstractLogger;
use Elements\Bundle\ProcessManagerBundle\Message\CheckCommandAliveMessage;
use Elements\Bundle\ProcessManagerBundle\Message\StopProcessMessage;
use Monolog\Logger;
use Pimcore\Bundle\ApplicationLoggerBundle\ApplicationLogger;
use Symfony\Component\Process\Process;

/**
 * Class MonitoringItem
 *
 * @method  MonitoringItem save() MonitoringItem
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
    public $id;

    /**
     * @var int
     */
    public $parentId;

    public $name;

    public $message;

    public $status = self::STATUS_UNKNOWN;

    public $command;

    public $creationDate;

    public $configurationId;

    public $reportedDate;

    public $modificationDate;

    public int|null $pid = null;

    public array $loggers = [];

    public array $actions = [];

    public int $executedByUser = 0;

    protected \Elements\Bundle\ProcessManagerBundle\Logger $logger;

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
    public $published = true;

    public $messengerPending = false;

    /**
     * @var string
     */
    public $group = '';

    /**
     * @var int | null
     */
    public $deleteAfterHours = null;

    /**
     * @var string
     */
    public $metaData = '';

    public $hasCriticalError = false;

    /**
     * Error Level which sould be considered as critical
     * ['critical','error','emergency']...
     *
     * @var array
     */
    public $criticalErrorLevel = [];

    public function getCriticalErrorLevel(): array
    {
        return $this->criticalErrorLevel;
    }

    /**
     * @param array $criticalErrorLevel
     *
     * @return $this
     */
    public function setCriticalErrorLevel($criticalErrorLevel)
    {
        $this->criticalErrorLevel = $criticalErrorLevel;

        return $this;
    }

    /**
     * @return int
     */
    public function getDeleteAfterHours()
    {
        return $this->deleteAfterHours;
    }

    /**
     * @param int $deleteAfterHours
     *
     * @return $this
     */
    public function setDeleteAfterHours($deleteAfterHours)
    {
        $this->deleteAfterHours = $deleteAfterHours;

        return $this;
    }

    public function getHasCriticalError(): bool
    {
        return $this->hasCriticalError;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     *
     * @return $this
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @param bool $hasCriticalError
     *
     * @return $this
     */
    public function setHasCriticalError($hasCriticalError)
    {
        $this->hasCriticalError = $hasCriticalError;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * @param string $metaData
     *
     * @return $this
     */
    public function setMetaData($metaData)
    {
        $this->metaData = $metaData;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->published;
    }

    /**
     * @param bool $published
     *
     * @return $this
     */
    public function setPublished($published)
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

    /**
     * @return string
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
     * @return bool
     */
    public function getIsDummy()
    {
        return $this->isDummy;
    }

    /**
     * @param bool $isDummy
     *
     * @return $this
     */
    public function setIsDummy($isDummy)
    {
        $this->isDummy = $isDummy;

        return $this;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public static function getById($id)
    {
        $self = new self();

        return $self->getDao()->getById($id);
    }

    public function setValues($data = [], bool $ignoreEmptyValues = false): static
    {
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $key => $value) {
                if(in_array($key,['callbackSettings','actions','loggers']) && is_string($value)){
                    $value = json_decode($value,true);
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
     * @return array
     */
    public function getActions()
    {
        $actions = $this->actions;
        if (is_string($actions)) {
            $actions = json_decode($actions, true, 512, JSON_THROW_ON_ERROR);
        }

        return $actions;
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
     * @return int
     */
    public function getTotalWorkload()
    {
        return $this->totalWorkload;
    }

    /**
     * @param int $totalWorkload
     *
     * @return $this
     */
    public function setTotalWorkload($totalWorkload)
    {
        $this->totalWorkload = $totalWorkload;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentWorkload()
    {
        return $this->currentWorkload;
    }

    /**
     * @param int $currentWorkload
     *
     * @return $this
     */
    public function setCurrentWorkload($currentWorkload)
    {
        $this->currentWorkload = $currentWorkload;

        return $this;
    }

    /**
     * @return Configuration
     */
    public function getProcessManagerConfigObject()
    {
        if ($id = $this->getConfigurationId()) {
            $config = Configuration::getById($id);

            return $config;
        }
    }

    /**
     * @return int
     */
    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    /**
     * @param int $currentStep
     *
     * @return $this
     */
    public function setCurrentStep($currentStep)
    {
        $this->currentStep = $currentStep;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalSteps()
    {
        return $this->totalSteps;
    }

    /**
     * @param int $totalSteps
     *
     * @return $this
     */
    public function setTotalSteps($totalSteps)
    {
        $this->totalSteps = $totalSteps;

        return $this;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setMessage(mixed $message, $logLevel = \Monolog\Logger::NOTICE)
    {
        $this->message = $message;
        if ($logLevel !== false) {
            $this->getLogger()->log($logLevel, $message);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return $this
     */
    public function setCommand(mixed $command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /** Returns true if the item died unexpectetly
     * @return bool
     */
    public function isAlive()
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

        return $this;
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

        return $this;
    }

    /**
     * convenience function to set the process message
     *
     * @param string $itemType
     * @param int $logLevel
     *
     * @return $this
     */
    public function setDefaultProcessMessage($itemType = 'item', $logLevel = Logger::NOTICE)
    {
        $currentWorkload = $this->getCurrentWorkload() ?: 1;

        $this->setMessage(
            'Processing '.$itemType.' '.$currentWorkload.' from '.$this->getTotalWorkload(
            ).' ('.($this->getTotalWorkload() - $currentWorkload).' remaining)',
            $logLevel
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function resetWorkload()
    {
        $this->setCurrentWorkload(0);
        $this->setTotalWorkload(0);

        return $this;
    }

    /**
     * @deprecated Use setWorkloadCompleted() instead
     *
     * @return $this
     */
    public function setWorloadCompleted()
    {
        return $this->setWorkloadCompleted();
    }

    /**
     * @return $this
     */
    public function setWorkloadCompleted()
    {
        $this->setCurrentWorkload($this->getTotalWorkload());

        return $this;
    }

    public function getDuration()
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
    }

    /**
     * @return mixed
     */
    public function getConfigurationId()
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
     * @return mixed
     */
    public function getPid()
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

    public function getLogFile()
    {
        return ElementsProcessManagerBundle::getLogDir().$this->getId().'.log';
    }

    /**
     * @return $this
     */
    public function deleteLogFile()
    {
        if (is_file($this->getLogFile())) {
            unlink($this->getLogFile());
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function resetState()
    {
        $this->setStatus(self::STATUS_UNKNOWN);
        $this->resetWorkload();
        $this->setCurrentStep(0)->setTotalSteps(0)->setPid(null);
        $this->setMessage('', false);
        $this->setCreationDate(time());
        $this->setModificationDate(time());
        $this->setHasCriticalError(0);

        return $this;
    }

    public function setCompleted()
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
     * @return array
     */
    public function getCallbackSettings()
    {
        return is_string($this->callbackSettings) ? json_decode($this->callbackSettings, true, 512, JSON_THROW_ON_ERROR) : $this->callbackSettings;
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
     * @param array $callbackSettings
     *
     * @return $this
     */
    public function setCallbackSettings(array $callbackSettings): self
    {
        $this->callbackSettings = $callbackSettings;
        return $this;
    }

    /**
     * Shorthand to get the values
     *
     * @return array
     */
    public function getConfigValues()
    {
        return $this->getProcessManagerConfigObject()->getExecutorClassObject()->getValues();
    }

    public function getReportValues()
    {
        $data = [];
        foreach (['id', 'pid', 'name', 'command', 'creationDate', 'modificationDate', 'callbackSettings'] as $field) {
            $data[$field] = $this->{'get'.ucfirst($field)}();
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function getReportedDate()
    {
        return $this->reportedDate;
    }

    /**
     * @return $this
     */
    public function setReportedDate(mixed $reportedDate)
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

    /**
     * @return int
     */
    public function getExecutedByUser(): int
    {
        return (int)$this->executedByUser;
    }

    /**
     * @param int $executedByUser
     *
     * @return $this
     */
    public function setExecutedByUser($executedByUser): static
    {
        $this->executedByUser = (int)$executedByUser;

        return $this;
    }

    /**
     * @return array
     */
    public function getLoggers(): array
    {
        $loggers = $this->loggers;
        if (is_string($loggers)) {
            $loggers = json_decode($loggers, true, 512, JSON_THROW_ON_ERROR);
        }

        return $loggers;
    }

    /**
     * @param array $loggers
     *
     * @return $this
     */
    public function setLoggers($loggers): static
    {
        $this->loggers = $loggers;

        return $this;
    }

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
            return round($this->getCurrentWorkload() / ($this->getTotalWorkload() / 100));
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

    public function getChildProcesses()
    {
        $list = new MonitoringItem\Listing();
        $list->setCondition('parentId = ?', [$this->getId()]);
        $list->setOrder('id');

        return $list->load();
    }

    public function getChildProcessesStatus(): array
    {
        $summary = ['active' => 0, 'failed' => 0, 'finished' => 0];
        $details = ['active' => [], 'failed' => [], 'finished' => []];
        $currentWorkload = 0;

        foreach($this->getChildProcesses() as $child) {
            $details[$child->getStatus()][] = ['id' => $child->getId(), 'message' => $child->getMessage(), 'alive' => $child->isAlive()];

            $currentWorkload += $child->getCurrentWorkload();

            if($child->getStatus() == self::STATUS_FINISHED) {
                $summary['finished']++;
            } else {
                if($child->isAlive()) {
                    $summary['active']++;
                } else {
                    $summary['failed']++;
                }
            }
        }

        return [
            'summary' => $summary,
            'details' => $details,
            'currentWorkload' => $currentWorkload,
        ];
    }
}

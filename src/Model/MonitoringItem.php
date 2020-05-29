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
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use function Symfony\Component\Debug\Tests\testHeader;

/**
 * Class MonitoringItem
 *
 * @method  MonitoringItem save() MonitoringItem
 * @method  MonitoringItem load() []MonitoringItem
 */
class MonitoringItem extends \Pimcore\Model\AbstractModel
{
    const STATUS_UNKNOWN = 'unknown';

    const STATUS_FINISHED = 'finished';

    const STATUS_FINISHED_WITH_ERROR = 'finished_with_errors';

    const STATUS_FAILED = 'failed';

    const STATUS_RUNNING = 'running';

    const STATUS_INITIALIZING = 'initializing';

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

    public $pid;

    public $loggers = [];

    /**
     * @var array
     */
    public $actions = [];

    /**
     * @var int
     */
    public $executedByUser = 0;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var array
     */
    public $callbackSettings = [];
    /**
     * @var int
     */
    public $totalWorkload;

    /**
     * @var int
     */
    public $currentWorkload;

    /**
     * @var int
     */
    public $currentStep = 0;

    /**
     * @var int
     */
    public $totalSteps = 1;

    /**
     * The dummy object won't be saved
     * It is just created when no monitoring item id is passed
     * so we dont have to check if the monitoring item is available
     *
     * @var bool
     */
    public $isDummy = false;

    /**
     * @var bool
     */
    public $published = true;

    /**
     * @var string
     */
    public $group = '';

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

    /**
     * @return array
     */
    public function getCriticalErrorLevel(): array
    {
        return $this->criticalErrorLevel;
    }

    /**
     * @param array $criticalErrorLevel
     * @return $this
     */
    public function setCriticalErrorLevel($criticalErrorLevel)
    {
        $this->criticalErrorLevel = $criticalErrorLevel;
        return $this;
    }

    /**
     * @return bool
     */
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
     * @return $this
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }



    /**
     * @param bool $hasCriticalError
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

    public function setValues($data = [])
    {
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $key => $value) {
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
            $actions = json_decode($actions, true);
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
     * @param mixed $message
     *
     * @return $this
     */
    public function setMessage($message, $logLevel = \Monolog\Logger::NOTICE)
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
     * @param mixed $command
     *
     * @return $this
     */
    public function setCommand($command)
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
        if ($pid = $this->getPid()) {
            $checks = 0;
            while(self::getById($this->getId()) == self::STATUS_INITIALIZING){ //check for state because shortly after the background execution the process is alive...
                usleep(500000);
                $checks++;
                if($checks > 10){
                    break; //just to make sure we do not end in a endlessloop
                }
            }
            return posix_getpgid($pid) ? true: false ;
        }else{
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

    /**
     * @param mixed $creationDate
     */
    public function setCreationDate($creationDate)
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

    /**
     * @param mixed $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * convenience function to set the process message
     *
     * @param string $itemType
     *
     * @return $this
     */
    public function setDefaultProcessMessage($itemType = 'item')
    {
        $currentWorkload = $this->getCurrentWorkload() ?: 1;

        $this->setMessage(
            'Processing '.$itemType.' '.$currentWorkload.' from '.$this->getTotalWorkload(
            ).' ('.($this->getTotalWorkload() - $currentWorkload).' remaining)'
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
     * @param mixed $configurationId
     *
     * @return $this
     */
    public function setConfigurationId($configurationId)
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
     * @param mixed $pid
     *
     * @return $this
     */
    public function setPid($pid)
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
        $this->setMessage('');
        $this->setCreationDate(time());
        $this->setModificationDate(time());

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
        return is_string($this->callbackSettings) ? json_decode($this->callbackSettings, true) : $this->callbackSettings;
    }

    public function getCallbackSettingsForGrid()
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
    public function setCallbackSettings($callbackSettings)
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
     * @param mixed $reportedDate
     *
     * @return $this
     */
    public function setReportedDate($reportedDate)
    {
        $this->reportedDate = $reportedDate;

        return $this;
    }

    /**
     * @return \Pimcore\Log\ApplicationLogger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new \Elements\Bundle\ProcessManagerBundle\Logger();
            $this->logger->setComponent($this->getName());
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
    public function getExecutedByUser()
    {
        return (int)$this->executedByUser;
    }

    /**
     * @param int $executedByUser
     *
     * @return $this
     */
    public function setExecutedByUser($executedByUser)
    {
        $this->executedByUser = (int)$executedByUser;

        return $this;
    }

    /**
     * @return array
     */
    public function getLoggers()
    {
        $loggers = $this->loggers;
        if (is_string($loggers)) {
            $loggers = json_decode($loggers, true);
        }

        return $loggers;
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

    public function getForWebserviceExport()
    {
        $data = $this->getObjectVars();

        $data['callbackSettings'] = json_decode($data['callbackSettings'], true);
        $data['actions'] = json_decode($data['actions'], true);
        $data['loggers'] = json_decode($data['loggers'], true);
        unset($data['command']);

        return $data;
    }

    /**
     * @return int|null
     */
    public function getProgressPercentage()
    {
        if ($this->getCurrentWorkload() && $this->getTotalWorkload()) {
            return round($this->getCurrentWorkload() / ($this->getTotalWorkload() / 100));
        }

        return null;
    }


    public function stopProcess(){
        $pid = $this->getPid();
        if($pid){
            $this->setPid(null)->setStatus(self::STATUS_FAILED)->save();
            $res = \Pimcore\Tool\Console::exec('kill -9 '.$pid);
            return !$this->isAlive();
        }
    }

    public function getChildProcesses(){
        $list = new MonitoringItem\Listing();
        $list->setCondition('parentId = ?',[$this->getId()]);
        $list->setOrder('id');
        return $list->load();
    }

    public function getChildProcessesStatus(){
        $result = ['active' => 0, 'failed' => 0,'finished' => 0];

        $satus = [];

        foreach($this->getChildProcesses() as $child){
            $status[$child->getStatus()][] = ['id' => $child->getId(),'message' => $child->getMessage(),'alive' => $child->isAlive()];

            if($child->getStatus() == self::STATUS_FINISHED){
                $result['finished']++;
            }else{
                if($child->isAlive()){
                    $result['active']++;
                }else{
                    $result['failed']++;
                }
            }
        }

        return ['summary' => $result,'details' => $status];
    }
}

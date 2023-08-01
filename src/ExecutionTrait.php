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

namespace Elements\Bundle\ProcessManagerBundle;

use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Monolog\Logger;

trait ExecutionTrait
{
    protected $commandObject;

    protected static $childProcessErrorHandling = 'strict';

    protected static $childProcessCheckInterval = 500000; //microseconds

    /**
     * @param int $microseconds
     */
    public static function setChildProcessCheckInterval($microseconds)
    {
        self::$childProcessCheckInterval = $microseconds;
    }

    protected static function getCommand($options)
    {
        global $argv;
        $command = !empty($options['command']) ? $options['command'] : implode(' ', $argv);

        return trim((string) $command);
    }

    public static function getChildProcessErrorHandling(): string
    {
        return static::$childProcessErrorHandling;
    }

    /**
     * @param string $childProcessErrorHandling
     *
     * @return $this
     */
    public static function setChildProcessErrorHandling($childProcessErrorHandling)
    {
        static::$childProcessErrorHandling = $childProcessErrorHandling;
    }

    /**
     * @param $monitoringId
     * @param array $options
     *
     * @return MonitoringItem
     */
    public static function initProcessManager(string $monitoringId, $options = [])
    {
        if (!ElementsProcessManagerBundle::getMonitoringItem(false)) {
            if(!array_key_exists('autoCreate', $options)) {
                $options['autoCreate'] = false;
            }
            $monitoringItem = null;
            if ($monitoringId) {
                $monitoringItem = MonitoringItem::getById($monitoringId);
                ElementsProcessManagerBundle::setMonitoringItem($monitoringItem);
            } elseif(getenv(ElementsProcessManagerBundle::MONITORING_ITEM_ENV_VAR)) { //check for env passed
                $monitoringId = (string)getenv(ElementsProcessManagerBundle::MONITORING_ITEM_ENV_VAR);
                $monitoringItem = MonitoringItem::getById($monitoringId);
                ElementsProcessManagerBundle::setMonitoringItem($monitoringItem);
            }

            if ($options['autoCreate'] && !$monitoringItem) {
                $options['command'] = self::getCommand($options);

                if(!array_key_exists('name', $options)) {
                    $commandParts = explode_and_trim(' ', $options['command']);
                    $options['name'] = $commandParts[1] ?? '';
                }

                $monitoringItem = new MonitoringItem();
                $monitoringItem->setValues($options);

                if ($configId = $monitoringItem->getConfigurationId()) {
                    $config = Configuration::getById($configId);
                    if ($executor = $config->getExecutorClassObject()) {
                        $monitoringItem->setLoggers($executor->getLoggers());
                    }
                }

                unset($options['id']);

                /**
                 * only set console logger if dont pass loggers or the config doesn't have loggers
                 */
                if (empty($options['loggers']) && empty($monitoringItem->getLoggers())) {
                    $monitoringItem->setLoggers([
                        [
                            'logLevel' => 'DEBUG',
                            'simpleLogFormat' => 'on',
                            'class' => '\\' . \Elements\Bundle\ProcessManagerBundle\Executor\Logger\Console::class
                        ]
                    ]);
                }

                $monitoringItem->setStatus($monitoringItem::STATUS_INITIALIZING);
                $monitoringItem->setPid(getmypid());
                $monitoringItem->save();
                $monitoringId = $monitoringItem->getId();
                ElementsProcessManagerBundle::setMonitoringItem($monitoringItem);
            }

            $monitoringItem = ElementsProcessManagerBundle::getMonitoringItem();
            if ($monitoringId) {
                if ($monitoringItem && $monitoringItem->getConfigurationId()) {
                    $config = Configuration::getById((string)$monitoringItem->getConfigurationId());
                    if ($config) {
                        if (!$monitoringItem->getName()) {
                            $monitoringItem->setName($config->getName())->save();
                        }
                        if (!$config->getActive()) {
                            exit('ProcessManager: Config with ID ' . $config->getId().' is disabled - exiting');
                        }
                    }
                    $values = $config ? $config->getExecutorClassObject()->getValues() : $options;
                    if (!empty($values['uniqueExecution']) && !$monitoringItem->getParentId()) { //dont check if it is a child process
                        self::doUniqueExecutionCheck($config, $options);
                    }
                }

                $options['monitoringItem'] = $monitoringItem;

                if (!defined('PIMCORE_CONSOLE') || !PIMCORE_CONSOLE) {
                    register_shutdown_function(function ($arguments) {
                        ElementsProcessManagerBundle::shutdownHandler($arguments);
                    }, $options);
                }
                ElementsProcessManagerBundle::startup($options);

                $monitoringItem->setCurrentStep($options['currentStop'] ?? 1)
                    ->setTotalSteps($options['totalSteps'] ?? 1)->setStatus(MonitoringItem::STATUS_RUNNING)->save();
            }
        }
        self::checkExecutingUser(ElementsProcessManagerBundle::getConfiguration()->getAdditionalScriptExecutionUsers());

        return ElementsProcessManagerBundle::getMonitoringItem();
    }

    /**
     * @param $config
     * @param $options
     */
    protected static function doUniqueExecutionCheck($config, $options)
    {
        //when we have a config we check for the config id to determine the processes
        if ($config) {
            $processesRunning = $config->getRunningProcesses();
        } else {
            $list = new MonitoringItem\Listing();
            $list->setCondition('command = ? AND pid <> ""', [$options['command']]);
            $processesRunning = [];
            foreach ($list->load() as $item) {
                if ($item->isAlive()) {
                    $processesRunning[] = $item;
                }
            }
        }

        //remove own pid
        foreach ($processesRunning as $i => $item) {
            if ($item->getPid() == getmypid()) {
                unset($processesRunning[$i]);
            }
        }

        if ($count = count($processesRunning)) {
            foreach ($processesRunning as $process) {
                //only delete the item if the other process doesn't use the same monitoring ID
                if ($process->getId() != ElementsProcessManagerBundle::getMonitoringItem()->getId()) {
                    ElementsProcessManagerBundle::getMonitoringItem()->delete();
                }
            }
            ElementsProcessManagerBundle::getMonitoringItem()->getLogger()->info('Another process with the PID ' . getmypid().' started. Exiting Process:' . getmypid());
            ElementsProcessManagerBundle::setMonitoringItem(null);
            exit("\n\nProcessManager: $count ".($count > 1 ? 'processes running' : 'process running'). " - exiting\n\n");
        }
    }

    /**
     * @return MonitoringItem
     */
    public static function getMonitoringItem()
    {
        return ElementsProcessManagerBundle::getMonitoringItem();
    }

    /**
     * @return mixed
     */
    public function getCommandObject()
    {
        return $this->commandObject;
    }

    /**
     * @return $this
     */
    public function setCommandObject(mixed $commandObject)
    {
        $this->commandObject = $commandObject;

        return $this;
    }

    /**
     * @param array $allowedUsers
     *
     * @throws \Exception
     */
    public static function checkExecutingUser($allowedUsers = [])
    {
        $configFile = PIMCORE_WEB_ROOT.'/index.php';
        $owner = fileowner($configFile);

        if ($owner === false) {
            throw new \Exception("Couldn't get user from file " . $configFile);
        }
        if(function_exists('posix_getpwuid')) {
            $userData = posix_getpwuid($owner);
            if($userData) {
                $allowedUsers[] = $userData['name'];

                $scriptExecutingUserData = posix_getpwuid(posix_geteuid());
                $scriptExecutingUser = $scriptExecutingUserData['name'];

                if (!in_array($scriptExecutingUser, $allowedUsers)) {
                    throw new \Exception("The current system user is not allowed to execute this script. Allowed users: '" . implode(',', $allowedUsers) ."' Executing user: '$scriptExecutingUser'.");
                }
            }
        }

    }

    /**
     * @param int $numberOfchildProcesses number of child processes to run in parallel
     * @param array $workload workload to process
     * @param int $batchSize items to process per child process
     * @param null $callback callback to modifiy the monitoring item before start (e.g. alter actions,loggers...)
     * @param int $startAfterPackage Start after package (skip packages before)
     *
     * @throws \Exception
     */
    public static function executeChildProcesses(MonitoringItem $monitoringItem, array $workload, $numberOfchildProcesses = 5, $batchSize = 10, $callback = null, $startAfterPackage = null)
    {
        $workloadChunks = array_chunk($workload, $batchSize); //entries per process
        $childProcesses = $monitoringItem->getChildProcesses();
        foreach($childProcesses as $c) {
            $c->delete();
        }
        $monitoringItem->setCurrentWorkload(0)->setTotalWorkload(count($workload))->setMessage('Starting child processes')->save();

        $i = 0;
        foreach($workloadChunks as $i => $package) {

            if($startAfterPackage && $startAfterPackage > ($i + 1)) {
                $monitoringItem->getLogger()->debug('Skipping Package' . ($i + 1));

                continue;
            }

            $monitoringItem->setMessage('Processing batch '. ($i + 1) . ' of ' . count($workloadChunks))->save();

            for($x = 1; $x <= 3; $x++) {
                $result = Helper::executeJob($monitoringItem->getConfigurationId(), $monitoringItem->getCallbackSettings(), 0, json_encode($package, JSON_THROW_ON_ERROR), $monitoringItem->getId(), $callback);

                if($result['success'] == false) {
                    $attempts = $i === 1 ? "$i time" : "$i times";
                    $monitoringItem->getLogger()->warning("Can't start child (tried $attempts) - reason: " . $result['message']);

                    sleep(5);
                    if($x == 3) {
                        throw new \Exception("Can't start child  - reason: " . $result['message']);
                    }
                } else {
                    break;
                }
            }
            self::waitForChildProcesses($monitoringItem, $i * $batchSize, $numberOfchildProcesses);
        }
        self::waitForChildProcesses($monitoringItem, $i * $batchSize);
    }

    protected static function childProcessCheck(MonitoringItem $monitoringItem)
    {
        $statuses = $monitoringItem->getChildProcessesStatus();
        $monitoringItem->setModificationDate(time())->save();
        if($statuses['summary']['failed'] && static::getChildProcessErrorHandling() == 'strict') {
            foreach([MonitoringItem::STATUS_RUNNING, MonitoringItem::STATUS_INITIALIZING, MonitoringItem::STATUS_UNKNOWN] as $status) {
                $items = $statuses['details'][$status] ?? [];
                foreach((array)$items as $entry) {
                    $mItem = MonitoringItem::getById($entry['id']);

                    if($mItem) {
                        $mItem->stopProcess();
                        $mItem->setMessage('Killed by MonitoringItem ID '. $monitoringItem->getId(). ' because child process failed', false)->save();
                    }
                }
            }

            throw new \Exception('Exiting because child failed: ' .print_r($statuses['details'][MonitoringItem::STATUS_FAILED], true));
        }

    }

    /**
     * @param int $i
     * @param int $batchSize
     * @param int $numberOfchildProcesses
     *
     * @throws \Exception
     */
    protected static function waitForChildProcesses(MonitoringItem $monitoringItem, int $baseline, int $maxProcesses = 0): void
    {
        do {
            $status = $monitoringItem->getChildProcessesStatus();
            $activeProcesses = $status['summary']['active'];

            $monitoringItem->setCurrentWorkload($baseline + $status['currentWorkload'])->save();

            $monitoringItem->getLogger()->info('Waiting to start child processes -> status: ' . print_r($status['summary'], true));
            static::childProcessCheck($monitoringItem);
            usleep(static::$childProcessCheckInterval);
        } while ($activeProcesses > $maxProcesses);

        if ($status['summary']['failed'] > 0) {
            throw new \Exception('Child process failed');
        }
    }
}

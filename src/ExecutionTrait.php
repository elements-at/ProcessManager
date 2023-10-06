<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle;

use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Monolog\Logger;

trait ExecutionTrait
{
    protected mixed $commandObject;

    protected static string $childProcessErrorHandling = 'strict';

    protected static int $childProcessCheckInterval = 500000; //microseconds

    /**
     * @param int $microseconds
     */
    public static function setChildProcessCheckInterval(int $microseconds): void
    {
        self::$childProcessCheckInterval = $microseconds;
    }

    /**
     * @param array<mixed> $options
     *
     * @return string
     */
    protected static function getCommand(array $options): string
    {
        global $argv;
        $command = empty($options['command']) ? implode(' ', $argv) : $options['command'];

        return trim((string) $command);
    }

    public static function getChildProcessErrorHandling(): string
    {
        return static::$childProcessErrorHandling;
    }

    /**
     * @param string $childProcessErrorHandling
     *
     * @return void
     */
    public static function setChildProcessErrorHandling(string $childProcessErrorHandling): void
    {
        static::$childProcessErrorHandling = $childProcessErrorHandling;
    }

    /**
     * @param int|null $monitoringId
     * @param array<mixed> $options
     *
     * @return MonitoringItem|null
     *
     * @throws \Exception
     */
    public static function initProcessManager(?int $monitoringId, array $options = []): ?\Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem
    {
        if (!ElementsProcessManagerBundle::getMonitoringItem(false) instanceof \Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem) {
            if(!array_key_exists('autoCreate', $options)) {
                $options['autoCreate'] = false;
            }
            $monitoringItem = null;
            if ($monitoringId) {
                $monitoringItem = MonitoringItem::getById($monitoringId);
                ElementsProcessManagerBundle::setMonitoringItem($monitoringItem);
            } elseif(getenv(ElementsProcessManagerBundle::MONITORING_ITEM_ENV_VAR)) { //check for env passed
                $monitoringId = (int)getenv(ElementsProcessManagerBundle::MONITORING_ITEM_ENV_VAR);
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
                    $executor = $config->getExecutorClassObject();
                    $monitoringItem->setLoggers($executor->getLoggers());
                }

                unset($options['id']);

                /**
                 * only set console logger if dont pass loggers or the config doesn't have loggers
                 */
                if (empty($options['loggers']) && $monitoringItem->getLoggers() === []) {
                    $monitoringItem->setLoggers([
                        [
                            'logLevel' => 'DEBUG',
                            'simpleLogFormat' => 'on',
                            'class' => '\\' . \Elements\Bundle\ProcessManagerBundle\Executor\Logger\Console::class,
                        ],
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
                if ($monitoringItem instanceof \Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem) {
                    $config = Configuration::getById($monitoringItem->getConfigurationId());
                    if ($config instanceof \Elements\Bundle\ProcessManagerBundle\Model\Configuration) {
                        if (!$monitoringItem->getName()) {
                            $monitoringItem->setName($config->getName())->save();
                        }
                        if (!$config->getActive()) {
                            exit('ProcessManager: Config with ID ' . $config->getId().' is disabled - exiting');
                        }
                    }
                    $values = $config instanceof \Elements\Bundle\ProcessManagerBundle\Model\Configuration ? $config->getExecutorClassObject()->getValues() : $options;
                    if (!empty($values['uniqueExecution']) && !$monitoringItem->getParentId()) { //dont check if it is a child process
                        self::doUniqueExecutionCheck($config, $options);
                    }
                }

                $options['monitoringItem'] = $monitoringItem;

                if (!defined('PIMCORE_CONSOLE') || !PIMCORE_CONSOLE) {
                    register_shutdown_function(function ($arguments): void {
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
     * @param mixed $config
     * @param array<mixed> $options
     *
     * @return void
     */
    protected static function doUniqueExecutionCheck(mixed $config, array $options): void
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

        if (($count = count($processesRunning)) !== 0) {
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
     * @return MonitoringItem|null
     */
    public static function getMonitoringItem(): ?\Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem
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
     * @param array<mixed> $allowedUsers
     *
     * @return void
     *
     * @throws \Exception
     */
    public static function checkExecutingUser(array $allowedUsers = []): void
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
     * @param array<mixed> $workload workload to process
     * @param int $batchSize items to process per child process
     * @param null $callback callback to modifiy the monitoring item before start (e.g. alter actions,loggers...)
     * @param int $startAfterPackage Start after package (skip packages before)
     *
     * @return void
     *
     * @throws \Exception
     */
    public static function executeChildProcesses(MonitoringItem $monitoringItem, array $workload, int $numberOfchildProcesses = 5, int $batchSize = 10, callable $callback = null, $startAfterPackage = null): void
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

    /**
     * @param MonitoringItem $monitoringItem
     *
     * @return void
     *
     * @throws \Exception
     */
    protected static function childProcessCheck(MonitoringItem $monitoringItem): void
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
    /*
     * @param int $i
     * @param int $batchSize
     * @param int $numberOfchildProcesses
     *
     * @throws \Exception
     */

    /**
     * @param MonitoringItem $monitoringItem
     * @param int $baseline
     * @param int $maxProcesses
     *
     * @return void
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

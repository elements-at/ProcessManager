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

    protected static function getCommand($options)
    {
        global $argv;
        $command = $options['command'] ?: implode(' ', $argv);

        return trim($command);
    }

    /**
     * @param $monitoringId
     * @param array $options
     *
     * @return MonitoringItem
     */
    public static function initProcessManager($monitoringId, $options = [])
    {
        if (!ElementsProcessManagerBundle::getMonitoringItem(false)) {
            if ($monitoringId) {
                $monitoringItem = MonitoringItem::getById($monitoringId);
                ElementsProcessManagerBundle::setMonitoringItem($monitoringItem);
            }elseif(getenv(ElementsProcessManagerBundle::MONITORING_ITEM_ENV_VAR)){ //check for env passed
                $monitoringId = getenv(ElementsProcessManagerBundle::MONITORING_ITEM_ENV_VAR);
                $monitoringItem = MonitoringItem::getById($monitoringId);
                ElementsProcessManagerBundle::setMonitoringItem($monitoringItem);
            }

            if ($options['autoCreate'] && !$monitoringItem) {
                $options['command'] = self::getCommand($options);

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
                if (is_null($options['loggers']) && empty($monitoringItem->getLoggers())) {
                    $monitoringItem->setLoggers([
                        [
                            'logLevel' => 'DEBUG',
                            'simpleLogFormat' => 'on',
                            'class' => '\Elements\Bundle\ProcessManagerBundle\Executor\Logger\Console'
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
                if ($monitoringItem) {
                    $config = Configuration::getById($monitoringItem->getConfigurationId());
                    if ($config) {
                        if (!$monitoringItem->getName()) {
                            $monitoringItem->setName($config->getName())->save();
                        }
                        if (!$config->getActive()) {
                            exit('ProcessManager: Config with ID ' . $config->getId().' is disabled - exiting');
                        }
                    }
                    $values = $config ? $config->getExecutorClassObject()->getValues() : $options;
                    if ($values['uniqueExecution']) {
                        self::doUniqueExecutionCheck($config, $options);
                    }
                }

                $options['monitoringItem'] = $monitoringItem;

                if (!PIMCORE_CONSOLE) {
                    register_shutdown_function(function ($arguments) {
                        ElementsProcessManagerBundle::shutdownHandler($arguments);
                    }, $options);
                }
                ElementsProcessManagerBundle::startup($options);

                $monitoringItem->setCurrentStep($options['currentStop'] ?: 1)
                    ->setTotalSteps($options['totalSteps'] ?: 1)->setStatus(MonitoringItem::STATUS_RUNNING)->save();
            }
        }
        self::checkExecutingUser((array)ElementsProcessManagerBundle::getConfig()['general']['additionalScriptExecutionUsers']);

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
     * @param mixed $commandObject
     *
     * @return $this
     */
    public function setCommandObject($commandObject)
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
        $configFile = \Pimcore\Config::locateConfigFile('system.yml');
        $owner = fileowner($configFile);
        if ($owner === false) {
            throw new \Exception("Couldn't get user from file " . $configFile);
        }
        $userData = posix_getpwuid($owner);
        $allowedUsers[] = $userData['name'];

        $scriptExecutingUserData = posix_getpwuid(posix_geteuid());
        $scriptExecutingUser = $scriptExecutingUserData['name'];

        if (!in_array($scriptExecutingUser, $allowedUsers)) {
            throw new \Exception("The current system user is not allowed to execute this script. Allowed users: '" . implode(',', $allowedUsers) ."' Executing user: '$scriptExecutingUser'.");
        }
    }

    /**
     * @param MonitoringItem $monitoringItem
     * @param int $numberOfchildProcesses number of child processes to run in parallel
     * @param array $workload workload to process
     * @param int $batchSize items to process per child process
     * @param null $callback callback to modifiy the monitoring item before start (e.g. alter actions,loggers...)
     * @throws \Exception
     */
    protected function executeChildProcesses(MonitoringItem $monitoringItem,array $workload, $numberOfchildProcesses = 5, $batchSize = 10, $callback = null){
        $workload = array_chunk($workload,$batchSize); //entries per process
        $childProcesses = $monitoringItem->getChildProcesses();
        foreach($childProcesses as $c){
            $c->delete();
        }
        $monitoringItem->setCurrentWorkload(0)->setTotalWorkload(count($workload))->setMessage('Starting child processes')->save();

        foreach($workload as $i => $package){

            $monitoringItem->setCurrentWorkload($i+1)->setMessage('Processing package '. ($i+1))->save();

            $result = Helper::executeJob($monitoringItem->getConfigurationId(), $monitoringItem->getCallbackSettings(), 0,$package,$monitoringItem->getId(),$callback);

            while ($monitoringItem->getChildProcessesStatus()['summary']['active'] >= $numberOfchildProcesses){ //run x processes parrallel
                $monitoringItem->getLogger()->info('Waiting -> status: ' . print_r($monitoringItem->getChildProcessesStatus()['summary'],true));
                sleep(1);
            }

            if($monitoringItem->getChildProcessesStatus()['failed']){
                throw new \Exception('Childs failed');
            }

        }
    }
}

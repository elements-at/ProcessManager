<?php
namespace ProcessManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

trait ExecutionTrait {


    protected $commandObject;

    protected static function getCommand($options){
        GLOBAL $argv;
        $command = $options['command'] ?: implode(' ',$argv);
        return trim($command);
    }

    /**
     * @param $monitoringId
     * @param array $options
     * @return MonitoringItem
     */
    protected static function initProcessManager($monitoringId,$options = []){

        if(!Plugin::getMonitoringItem()){
            if($monitoringId){
                $monitoringItem = MonitoringItem::getById($monitoringId);
                Plugin::setMonitoringItem($monitoringItem);
            }

            if($options['autoCreate'] && !$monitoringItem){
                $options['command'] = self::getCommand($options);

                $monitoringItem = new MonitoringItem();
                unset($options['id']);
                $monitoringItem->setValues($options);
                $monitoringItem->setStatus($monitoringItem::STATUS_INITIALIZING);
                $monitoringItem->setPid(getmypid());
                $monitoringItem->save();
                $monitoringId = $monitoringItem->getId();
                Plugin::setMonitoringItem($monitoringItem);
            }

            $monitoringItem = Plugin::getMonitoringItem();
            if($monitoringId){
                if($monitoringItem){

                    $config = Configuration::getById($monitoringItem->getConfigurationId());
                    if($config){
                        if(!$monitoringItem->getName()){
                            $monitoringItem->setName($config->getName())->save();
                        }
                        if(!$config->getActive()){
                            exit("ProcessManager: Config with ID " . $config->getId().' is disabled - exiting');
                        }
                    }
                    $values = $config ? $config->getExecutorClassObject()->getValues() : $options;
                    if($values["uniqueExecution"]){
                        self::doUniqueExecutionCheck($config,$options);
                    }
                }

                $options['monitoringItem'] = $monitoringItem;
                register_shutdown_function(function($arguments){
                    Plugin::shutdownHandler($arguments);
                }, $options);
                Plugin::startup($options);

                $monitoringItem->setCurrentStep($options['currentStop'] ?: 1)
                    ->setTotalSteps($options['totalSteps'] ?: 1)->setStatus(MonitoringItem::STATUS_RUNNING)->save();
            }
        }

        return Plugin::getMonitoringItem();
    }

    /**
     * @param $config
     * @param $options
     */
    protected static function doUniqueExecutionCheck($config,$options){
        //when we have a config we check for the config id to determine the processes
        if($config){
            $processesRunning = $config->getRunningProcesses();
        }else{
            $list = new MonitoringItem\Listing();
            $list->setCondition('command = ? AND pid <> ""',[$options['command']]);
            $processesRunning = [];
            foreach($list->load() as $item){
                if($item->isAlive()){
                    $processesRunning[] = $item;
                }
            }
        }

        //remove own pid
        foreach($processesRunning as $i => $item){
            if($item->getPid() == getmypid()){
                unset($processesRunning[$i]);
            }
        }

        if($count = count($processesRunning)){
            foreach($processesRunning as $process){
                //only delete the item if the other process doesn't use the same monitoring ID
                if($process->getId() != Plugin::getMonitoringItem()->getId()){
                    Plugin::getMonitoringItem()->delete();
                }
            }
            Plugin::getMonitoringItem()->getLogger()->info('Another process with the PID ' . getmypid().' started. Exiting Process:' . getmypid());
            Plugin::setMonitoringItem(null);
            exit("\n\nProcessManager: $count ".($count > 1 ? 'processes running' : 'process running'). " - exiting\n\n");
        }
    }

    /**
     * @return \ProcessManager\MonitoringItem
     */
    public static function getMonitoringItem(){
        return \ProcessManager\Plugin::getMonitoringItem();
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
     * @return $this
     */
    public function setCommandObject($commandObject)
    {
        $this->commandObject = $commandObject;
        return $this;
    }

}
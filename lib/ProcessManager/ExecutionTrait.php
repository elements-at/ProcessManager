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

        $monitoringItem = MonitoringItem::getById($monitoringId);
        Plugin::setMonitoringItem($monitoringItem);

        if($options['autoCreate'] && !$monitoringItem){
            $options['command'] = self::getCommand($options);

            $monitoringItem = new MonitoringItem();
            $monitoringItem->setName($options['name']);
            $monitoringItem->setStatus($monitoringItem::STATUS_INITIALIZING);
            $monitoringItem->setPid(getmypid());
            foreach($options as $key => $value){
                $setter = "set" . ucfirst($key);
                if(method_exists($monitoringItem,$setter)){
                    $monitoringItem->$setter($value);
                }
            }
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
        return $monitoringItem;
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
            $processesRunning = $list->load();
        }

        //remove own pid -> is set as running when it is executed in the admin
        foreach($processesRunning as $i => $item){
            if($item->getPid() == getmypid()){
                unset($processesRunning[$i]);
            }
        }

        if($count = count($processesRunning)){
            Plugin::getMonitoringItem()->delete();
            Plugin::setMonitoringItem(null);
            exit("\n\nProcessManager: $count ".($count > 1 ? 'processes running' : 'process running'). " - exiting\n\n");
        }
    }

    /**
     * @return \ProcessManager\MonitoringItem
     */
    protected function getMonitoringItem(){
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
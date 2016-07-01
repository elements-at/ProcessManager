<?php

use ProcessManager\Configuration;
use ProcessManager\MonitoringItem;
use ProcessManager\Plugin;

class ProcessManager_IndexController extends \Pimcore\Controller\Action\Admin
{

    public function configurationListAction()
    {
        $this->checkPermission('plugin_process_manager_view');
        $data = [];
        $list = new Configuration\Listing();
        $list->setOrder('DESC');
        $list->setOrderKey('id');
        $list->setLimit($this->getParam('limit',25));
        $list->setOffset($this->getParam("start"));
        if($filterCondition = \Pimcore\Admin\Helper\QueryParams::getFilterCondition($this->getParam('filter'))){
            $list->setCondition($filterCondition);
        }

        foreach($list->load() as $item){
            $tmp = $item->getObjectVars();
            $tmp['command'] = $item->getCommand();
            $tmp['type'] = $item->getExecutorClassObject()->getName();
            $tmp['settings'] = $item->getExecutorClassObject()->getExtJsSettings();
            $tmp['active'] = (int)$tmp['active'];
            if($item->getCronJob()){
                $nextRunTs = $item->getNextCronJobExecutionTimestamp();
                if($nextRunTs){
                    $tmp['cronJob'] .= ' <br/>(Next run:'. date('Y-m-d H:i:s',$nextRunTs).')';
                }
            }
            $data[] = $tmp;
        }

        $this->_helper->json(['total' => $list->getTotalCount(),'success' => true,'data' => $data]);
    }

    public function configurationSaveAction(){
        $this->checkPermission('plugin_process_manager_configure');

        $data = \Zend_Json::decode($this->getParam('data'));
        $values = $data['values'];
        $executorConfig = $data['executorConfig'];

        $actions = $data['actions'];

        /**
         * @var $executorClass \ProcessManager\Executor\AbstractExecutor
         */
        $executorClass = new $executorConfig['class']();

        $executorClass->setValues($values)->setExecutorConfig($executorConfig)->setActions($actions);

        if(!$this->getParam('id')){
            $configuration = new \ProcessManager\Configuration();
            $configuration->setActive(true);
        }else{
            $configuration = Configuration::getById($this->getParam('id'));
        }
        foreach($values as $key => $v){
            $setter = "set" . ucfirst($key);
            if(method_exists($configuration,$setter)){
                $configuration->$setter(trim($v));
            }
        }
        $configuration->setExecutorClass($executorClass);
        $configuration->save();
        $this->_helper->json(['success' => true,'id' => $configuration->getId()]);


    }

    public function configurationDeleteAction()
    {
        $this->checkPermission('plugin_process_manager_configure');

        $config = Configuration::getById($this->getParam('id'));
        if($config instanceof Configuration){
            $config->delete();
        }
        $this->_helper->json(['success' => true]);
    }

    public function monitoringItemDeleteAction(){
        $entry  = MonitoringItem::getById($this->getParam('id'));
        if($entry){
            $entry->delete();
            $this->_helper->json(['success' => true]);
        }
        $this->_helper->json(['success' => false,'message' => "Couldn't delete entry"]);
    }

    public function configurationActivateDisableAction(){
        try{
            $config = Configuration::getById($this->getParam('id'));
            $config->setActive((int)$this->getParam('value'))->save();
            $this->_helper->json(['success' => true]);
        }catch(\Exception $e){
            $this->_helper->json(['success' => false,'message' => $e->getMessage()]);
        }
    }
    public function configurationExecuteAction(){
        $this->checkPermission('plugin_process_manager_execute');


        try{
            $config = Configuration::getById($this->getParam('id'));

            $callbackSettings = $this->getParam('callbackSettings') ? \Zend_Json::decode($this->getParam('callbackSettings')) : [];
            
            if($config->getExecutorClassObject()->getValues()['uniqueExecution']){
                $running = $config->getRunningProcesses();
                if(!empty($running)){

                    $msg = "Can't start the process because " . count($running) . ' process is running (ID: '.$running[0]->getId() .'). Please wait until this processes is finished.';
                    throw new \Exception($msg);
                }
            }
            $command = $config->getCommand();

            $monitoringItem = new MonitoringItem();
            $monitoringItem->setName($config->getName());
            $monitoringItem->setProcessManagerConfig($config);
            $monitoringItem->setStatus($monitoringItem::STATUS_INITIALIZING);
            $monitoringItem->setConfigurationId($config->getId());
            $monitoringItem->setCallbackSettings($callbackSettings);
            $monitoringItem->setExecutedByUser($this->getUser()->getId());
            $item = $monitoringItem->save();
            $command .= ' --monitoring-item-id='.$item->getId();
            $monitoringItem->getLogger()->info('Execution Command: ' . $command.' in Background');
            $monitoringItem->setCommand($command)->save();
            $pid = \Pimcore\Tool\Console::execInBackground($command);
            $monitoringItem->setPid($pid)->save();

            $this->_helper->json(['success' => true,'executedCommand' => $command,'monitoringItemId' => $item->getId()]);
        }catch(\Exception $e){
            $this->_helper->json(['success' => false,'message' => $e->getMessage()]);
        }
    }

    public function monitoringItemDeleteBatchAction(){
        $logLevels = array_filter(explode(',',$this->getParam('logLevels')));
        if(!empty($logLevels)){
            $list = new MonitoringItem\Listing();
            $conditions = [];
            foreach($logLevels as $loglevel){
                $conditions[] = ' status ="'.$loglevel.'" ';
            }
            $condition = implode(' OR ',$conditions);
            $list->setCondition($condition);
            $items = $list->load();
            foreach($items as $item){
                $item->delete();
            }
            $this->_helper->json(['success' => true]);
        }else{
            $this->_helper->json(['success' => false,'message' => 'No statuses -> didn\'t deleted logs. Please select at least one status']);
        }
    }

    public function restartMonitoringItemAction(){
        try {
            $monitoringItem  = MonitoringItem::getById($this->getParam('id'));
            $monitoringItem->deleteLogFile()->resetState()->save();
            \Pimcore\Tool\Console::execInBackground($monitoringItem->getCommand(),$monitoringItem->getLogFile());
            $this->_helper->json(['success' => true]);
        }catch(\Exception $e){
            $this->_helper->json(['success' => false,'message' => $e->getMessage()]);
        }
    }

    public function monitoringItemListAction(){
        $this->checkPermission('plugin_process_manager_view');
        $data = [];
        $list = new MonitoringItem\Listing();
        $list->setOrder('DESC');
        $list->setOrderKey('id');
        $list->setLimit($this->getParam('limit',25));

        $list->setOffset($this->getParam("start"));

        $sortingSettings = \Pimcore\Admin\Helper\QueryParams::extractSortingSettings($this->getAllParams());
        if ($sortingSettings['orderKey'] && $sortingSettings['order']) {
            $list->setOrderKey($sortingSettings['orderKey']);
            $list->setOrder($sortingSettings['order']);
        }




        $callbacks = [
          'executedByUser' => function($f){
              $db = \Pimcore\Db::get();
              $ids = $db->fetchCol("SELECT id FROM users where name LIKE " .$db->quote("%" . $f->value . "%")) ?: [0];
              return ' executedByUser IN( ' . implode(',',$ids) .') ';
          }
        ];
        if($filterCondition = \Pimcore\Admin\Helper\QueryParams::getFilterCondition($this->getParam('filter'),['id', 'o_id','pid'],true,$callbacks)){
            $list->setCondition($filterCondition);
        }

        $total = $list->getTotalCount();


        foreach($list->load() as $item){
            $tmp = $item->getObjectVars();
            unset($tmp['processManagerConfig']);
            $tmp['steps'] = '-';
            if($item->getTotalSteps() > 0 || $item->getCurrentStep()){
                $tmp['steps'] = $item->getCurrentStep().'/'.$item->getTotalSteps();
            }
            $tmp['duration'] = $item->getDuration() ?: '-';
            $tmp['progress'] = 0;


            if($tmp['executedByUser']){
                $user = \Pimcore\Model\User::getById($tmp['executedByUser']);
                if($user){
                    $tmp['executedByUser'] = $user->getName();
                }else{
                    $tmp['executedByUser'] = 'User id: ' . $tmp['executedByUser'];
                }
            }else{
                $tmp['executedByUser'] = 'System';
            }

            $logFile = 0;
            if(is_readable($item->getLogFile())){
                $content = trim(file_get_contents($item->getLogFile()));
                if($content){
                    $logFile = 1;
                }
            }
            $configObject = $item->getProcessManagerConfigObject();
            $tmp['action'] = '';
            if($configObject){
                $actions = $configObject->getExecutorClassObject()->getActions();
                foreach($actions as $action){
                    /**
                     * @var $class \ProcessManager\Executor\Action\AbstractAction
                     */
                    $class = new $action['class'];
                    if($s = $class->getGridActionHtml($item,$action)){
                        $tmp['action'] .= $s;
                    }
                }
            }
            $tmp['logFile'] = $logFile;
            $tmp['retry'] = 1;
            if($item->isAlive()){
                $tmp['retry'] = 0;
            }
            if($tmp['retry'] == 1){
                $config = Configuration::getById($item->getConfigurationId());
                if($config){
                    if($config->getActive() == 0){
                        $tmp['retry'] = 0;
                    }else{
                        if($config->getExecutorClassObject()->getValues()['uniqueExecution']){
                            $runningProcesses = $config->getRunningProcesses();
                            if(!empty($runningProcesses)){
                                $tmp['retry'] = 0;
                            }
                        }
                    }

                }
            }
            $tmp['isAlive'] = $item->isAlive();
            if($item->getCurrentWorkload() && $item->getTotalWorkload()){
                $tmp['progress'] = round($item->getCurrentWorkload()/($item->getTotalWorkload()/100));
            }
            $tmp['callbackSettings'] = '<pre>' .print_r($item->getCallbackSettings(),true).'</pre>';
            $tmp['callbackSettings'] = $item->getCallbackSettingsForGrid();
            #$tmp['callbackSettings'] = '<table><tr><td><th>Key</th><th>Value</th></td></tr><tr><td>name:</td><td>testaa</td></tr></table>';
            $data[] = $tmp;
        }

        $this->_helper->json(['success' => true,'total' => $total, 'data' => $data]);
    }

    public function getPluginConfigAction(){
        $this->checkPermission('plugin_process_manager_view');
        $data = [
            'executorClass' => [],
            'executorActionClasses' => [],
        ];

        $pluginConfig = Plugin::getPluginConfig();

        foreach((array)$pluginConfig['executorClasses'] as $class => $config){
            if(\Pimcore\Tool::classExists($class)){
                $o = new $class($config);
                if($o instanceof \ProcessManager\Executor\AbstractExecutor){
                    $data['executorClass'][$o->getName()]['name'] = $o->getName();
                    $data['executorClass'][$o->getName()]['class'] = '\\'.get_class($o);
                    $data['executorClass'][$o->getName()]['config'] = $o->getConfig();
                    $data['executorClass'][$o->getName()]['extJsConfigurationClass'] = $o->getExtJsConfigurationClass();
                }
            }
        }

        foreach((array)$pluginConfig['executorActionClasses'] as $class => $config){
            if(\Pimcore\Tool::classExists($class)){
                $o = new $class($config);
                if($o instanceof \ProcessManager\Executor\Action\AbstractAction){
                    $data['executorActionClasses'][$o->getName()]['name'] = $o->getName();
                    $data['executorActionClasses'][$o->getName()]['class'] = '\\'.get_class($o);
                    $data['executorActionClasses'][$o->getName()]['config'] = $o->getConfig();
                    $data['executorActionClasses'][$o->getName()]['extJsClass'] = $o->getExtJsClass();
                }
            }
        }

        foreach((array)$pluginConfig['executorCallbackClasses'] as $class => $config){
            if(\Pimcore\Tool::classExists($class)){
                $o = new $class($config);
                if($o instanceof \ProcessManager\Executor\Callback\AbstractCallback){
                    $data['executorCallbackClasses'][$o->getName()]['name'] = $o->getName();
                    $data['executorCallbackClasses'][$o->getName()]['class'] = '\\'.get_class($o);
                    $data['executorCallbackClasses'][$o->getName()]['config'] = $o->getConfig();
                    $data['executorCallbackClasses'][$o->getName()]['extJsClass'] = $o->getExtJsClass();
                }
            }
        }

        $pimcoreCommands = [];

        $application = new Pimcore\Console\Application();
        foreach($application->all() as $key => $command){
            $tmp = ['description' => $command->getDescription(),'options' => $command->getDefinition()->getOptions()];

            if(!in_array($key,['help','list','update'])){
                $pimcoreCommands[$key] = $tmp;
            }
        }
        ksort($pimcoreCommands);
        $data['pimcoreCommands'] = $pimcoreCommands;

        $this->_helper->json($data);

    }

    public function monitoringItemLogAction(){
        $monitoringItem = MonitoringItem::getById($this->getParam('id'));
        $data = file_get_contents($monitoringItem->getLogFile());

        $data = explode("\n",$data);
        $data = array_reverse($data);
        foreach($data as $i => $row){
            if($row){
                if(strpos($row,'.WARNING')){
                    $data[$i] = '<span style="color:#ffb13b">' .$row.'</span>';
                }
                if(strpos($row,'.ERROR')){
                    $data[$i] = '<span style="color:#ff131c">' .$row.'</span>';
                }
            }
        }
        $data = implode("\n",$data);
        $this->view->data = $data;
        $this->view->monitoringItem = $monitoringItem;
    }

    public function downloadAction(){

        $monitoringItem = MonitoringItem::getById($this->getParam('id'));
        $executor = $monitoringItem->getProcessManagerConfigObject()->getExecutorClassObject();
        $actions = $executor->getActions();
        foreach($actions as $action){
            if($action['accessKey'] == $this->getParam('accessKey')){
                $className = $action['class'];
                /**
                 * @var $class \ProcessManager\Executor\Action\AbstractAction
                 */
                $class = new $className();
                $class->execute($monitoringItem,$action);
            }
        }

    }


}

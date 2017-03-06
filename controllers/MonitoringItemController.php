<?php

use ProcessManager\Configuration;
use ProcessManager\MonitoringItem;
use ProcessManager\CallbackSetting;
use ProcessManager\Plugin;

class ProcessManager_MonitoringItemController extends \Pimcore\Controller\Action\Admin
{

    public function listAction(){
        $this->checkPermission('plugin_pm_permission_view');
        $data = [];
        $list = new MonitoringItem\Listing();
        $list->setOrder('DESC');
        $list->setOrderKey('id');
        $list->setLimit($this->getParam('limit',25));
        $list->setUser($this->user);

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
           /* if(is_readable($item->getLogFile())){
                $content = trim(file_get_contents($item->getLogFile()));
                if($content){
                    $logFile = 1;
                }
            }*/
            $tmp['action'] = '';

            if($actions = $item->getActions()) {
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

            $tmp['logger'] = '';
            if($loggers = $item->getLoggers()) {
                foreach((array)$loggers as $i => $logger){
                    /**
                     * @var $class \ProcessManager\Executor\Logger\AbstractLogger
                     */
                    $class = new $logger['class'];
                    if(\Pimcore\Tool::classExists(get_class($class))){
                        $logger['index'] = $i;
                        if($s = $class->getGridLoggerHtml($item,$logger)){
                            $tmp['logger'] .= $s;
                        }
                    }
                }
            }

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

            $tmp['progress'] = '-';
            if($item->getCurrentWorkload() && $item->getTotalWorkload()){
                $progress = $item->getProgressPercentage();
                $tmp['progress'] = '<div class="x-progress x-progress-default x-border-box" style="width:100%;"><div class="x-progress-text x-progress-text-back">'.$progress.'%</div><div class="x-progress-bar x-progress-bar-default" style="width:'.$progress.'%"><div class="x-progress-text"><div>'.$progress.'%</div></div></div></div>';
            }

            $tmp['callbackSettingsString'] = \Zend_Json::encode($item->getCallbackSettings());
            $tmp['callbackSettings'] = $item->getCallbackSettingsForGrid();
            #$tmp['callbackSettings'] = '<table><tr><td><th>Key</th><th>Value</th></td></tr><tr><td>name:</td><td>testaa</td></tr></table>';
            $data[] = $tmp;
        }

        $this->_helper->json(['success' => true,'total' => $total, 'data' => $data]);
    }

    public function logApplicationLoggerAction()
    {
        try {
            $monitoringItem = MonitoringItem::getById($this->getParam('id'));

            if(!$monitoringItem){
                throw new \Exception('Monitoring Item with id' . $this->getParam('id').' not found');
            }
            $loggerIndex = $this->getParam('loggerIndex');
            if ($loggers = $monitoringItem->getLoggers()) {
                foreach ((array)$loggers as $i => $config) {
                    /**
                     * @var $class \ProcessManager\Executor\Logger\AbstractLogger
                     * @var $logger \ProcessManager\Executor\Logger\Application
                     */
                    $class = new $config['class'];
                    if (\Pimcore\Tool::classExists(get_class($class))) {

                        if ($i == $loggerIndex) {
                            $logger = $class;
                            if (!$config['logLevel']) {
                                $config['logLevel'] = 'DEBUG';
                            }
                            break;
                        }
                    }
                }
            }

            $result = $monitoringItem->getObjectVars();
            $result['logLevel'] = strtolower($config['logLevel']);

            $this->_helper->json(['success' => true,'data' => $result]);
        }catch(\Exception $e){
            $this->_helper->json(['success' => false,'message' => $e->getMessage()]);
        }
    }

    public function logFileLoggerAction(){
        $monitoringItem = MonitoringItem::getById($this->getParam('id'));

        $loggerIndex = $this->getParam('loggerIndex');
        if($loggers = $monitoringItem->getLoggers()) {
            foreach((array)$loggers as $i => $config){
                /**
                 * @var $class \ProcessManager\Executor\Logger\AbstractLogger
                 * @var $logger \ProcessManager\Executor\Logger\File
                 */
                $class = new $config['class'];
                if(\Pimcore\Tool::classExists(get_class($class))){

                    if($i == $loggerIndex){
                        $logger = $class;
                        $logFile = $logger->getLogFile($config,$monitoringItem);
                        if(!$config['logLevel']){
                            $config['logLevel'] = 'DEBUG';
                        }
                        break;
                    }
                }
            }
        }
        $this->view->logLevel = $config['logLevel'];
        $this->view->logFile = $logFile;

        if(is_readable($logFile)){

            $data = file_get_contents($logFile);

            $data = explode("\n",$data);
            foreach($data as $i => $row){
                if($row){
                    if(strpos($row,'.WARNING')){
                        $data[$i] = '<span style="color:#ffb13b">' .$row.'</span>';
                    }
                    if(strpos($row,'.ERROR')){
                        $data[$i] = '<span style="color:#ff131c">' .$row.'</span>';
                    }
                    if(strpos($row,'dev-server > ') === 0 || strpos($row,'production-server > ') === 0){
                        $data[$i] = '<span style="color:#35ad33">' .$row.'</span>';
                    }
                    foreach(['[echo]','[mkdir]','[delete]','[copy]'] as $k){
                        if(strpos($row,$k)){
                            $data[$i] = '<span style="color:#49b7d4">' .$row.'</span>';
                        }
                    }
                }
            }
        }else{
            $data = ["Log file doesn't exist. " .  $logFile];
        }
        $data = implode("\n",$data);
        $this->view->data = $data;
        $this->view->monitoringItem = $monitoringItem;
    }


    public function deleteAction(){
        $entry  = MonitoringItem::getById($this->getParam('id'));
        if($entry){
            $entry->delete();
            $this->_helper->json(['success' => true]);
        }
        $this->_helper->json(['success' => false,'message' => "Couldn't delete entry"]);
    }

    public function deleteBatchAction(){
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

    public function cancelAction() {
        $monitoringItem  = MonitoringItem::getById($this->getParam('id'));
        try {
            $pid = $monitoringItem->getPid();
            if($pid){
                $message = 'Process with PID "' . $pid . '" killed by Backend User: ' . $this->getUser()->getName();
                $monitoringItem->getLogger()->warning($message);
                $monitoringItem->setPid(null)->setStatus($monitoringItem::STATUS_FAILED)->save();
                \Pimcore\Tool\Console::exec('kill -9 ' . $pid);
            }
            $this->_helper->json(['success' => true]);
        }catch (\Exception $e){
            $this->_helper->json(['success' => false,'message' => $e->getMessage()]);
        }

    }

    public function restartAction(){
        try {
            $monitoringItem  = MonitoringItem::getById($this->getParam('id'));
            $monitoringItem->deleteLogFile()->resetState()->save();
            \Pimcore\Tool\Console::execInBackground($monitoringItem->getCommand(),$monitoringItem->getLogFile());
            $this->_helper->json(['success' => true]);
        }catch(\Exception $e){
            $this->_helper->json(['success' => false,'message' => $e->getMessage()]);
        }
    }
}

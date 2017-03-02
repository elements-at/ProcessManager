<?php

use ProcessManager\Configuration;
use ProcessManager\MonitoringItem;
use ProcessManager\CallbackSetting;
use ProcessManager\Plugin;

class ProcessManager_ConfigController extends \Pimcore\Controller\Action\Admin
{
    public function listAction()
    {
        $this->checkPermission('plugin_pm_permission_view');
        $data = [];
        $list = new Configuration\Listing();
        $list->setOrder('DESC');
        $list->setOrderKey('id');
        $list->setLimit($this->getParam('limit',25));
        $list->setOffset($this->getParam("start"));

        $list->setUser($this->user);

        if($filterCondition = \Pimcore\Admin\Helper\QueryParams::getFilterCondition($this->getParam('filter'))){
            $list->setCondition($filterCondition);
        }

        foreach($list->load() as $item){
            $tmp = $item->getObjectVars();

            try {

                $tmp['command'] = $item->getCommand();
                $tmp['type'] = $item->getExecutorClassObject()->getName();
                $tmp['extJsSettings'] = $item->getExecutorClassObject()->getExtJsSettings();

                $tmp['active'] = (int)$tmp['active'];
                if($item->getCronJob()){
                    $nextRunTs = $item->getNextCronJobExecutionTimestamp();
                    if($nextRunTs){
                        $tmp['cronJob'] .= ' <br/>(Next run:'. date('Y-m-d H:i:s',$nextRunTs).')';
                    }
                }
                $data[] = $tmp;
            }catch(\Exception $e){

            }

        }

        $this->_helper->json(['total' => $list->getTotalCount(),'success' => true,'data' => $data]);
    }

    public function saveAction(){
        $this->checkPermission('plugin_pm_permission_configure');

        $data = \Zend_Json::decode($this->getParam('data'));


        $values = $data['values'];
        $executorConfig = $data['executorConfig'];

        $actions = $data['actions'];
        /**
         * @var $executorClass \ProcessManager\Executor\AbstractExecutor
         * @var $configuration \ProcessManager\Configuration
         */
        $executorClass = new $executorConfig['class']();
        $executorClass->setValues($data['values']);
        $executorClass->setActions($data['actions']);
        $executorClass->setLoggers($data['loggers']);

       # $executorClass->setValues($values)->setExecutorConfig($executorConfig)->setActions($actions);
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
        $configuration->setExecutorClass($executorConfig['class']);
        $configuration->setExecutorSettings($executorClass->getStorageValue());
        $configuration->save();
        $this->_helper->json(['success' => true,'id' => $configuration->getId()]);
    }

    public function deleteAction()
    {
        $this->checkPermission('plugin_pm_permission_configure');

        $config = Configuration::getById($this->getParam('id'));
        if($config instanceof Configuration){
            $config->delete();
        }
        $this->_helper->json(['success' => true]);
    }

    public function activateDisableAction(){
        try{
            $config = Configuration::getById($this->getParam('id'));
            $config->setActive((int)$this->getParam('value'))->save();
            $this->_helper->json(['success' => true]);
        }catch(\Exception $e){
            $this->_helper->json(['success' => false,'message' => $e->getMessage()]);
        }
    }

    public function executeAction(){
        $this->checkPermission('plugin_pm_permission_execute');
        $callbackSettings = $this->getParam('callbackSettings') ? \Zend_Json::decode($this->getParam('callbackSettings')) : [];
        $result = \ProcessManager\Helper::executeJob($this->getParam('id'),$callbackSettings,$this->getUser()->getId());
        $this->_helper->json($result);
    }
}

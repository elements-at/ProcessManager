<?php

use ProcessManager\Configuration;
use ProcessManager\MonitoringItem;
use ProcessManager\CallbackSetting;
use ProcessManager\Plugin;

class ProcessManager_CallbackSettingsController extends \Pimcore\Controller\Action\Admin
{

    public function saveAction(){
        try{
            $values = \Zend_Json::decode($this->getParam('values'));
            $settings = \Zend_Json::decode($this->getParam('settings'));
            if($this->getParam('id')){
                $setting = CallbackSetting::getById($this->getParam('id'));
            }else{
                $setting = new CallbackSetting();
            }

            $setting = $setting->setName($values['name'])
                ->setDescription($values['description'])
                ->setType($this->getParam('type'))
                ->setSettings($this->getParam('settings'))->save();
            $this->_helper->json(['success' => true,'id' => $setting->getId()]);
        }catch (\Exception $e){
            $this->_helper->json(['success' => false,'message' => $e->getMessage()]);
        }
    }

    public function deleteAction(){
        try{
            $setting = CallbackSetting::getById($this->getParam('id'));
            $setting->delete();
            $this->_helper->json(['success' => true]);
        }catch (\Exception $e){
            $this->_helper->json(['success' => false,'message' => $e->getMessage()]);
        }
    }

    public function copyAction(){
        try{
            $setting = CallbackSetting::getById($this->getParam('id'));
            if($setting){
                $setting->setId(null)->setName('Copy - '.$setting->getName())->save();
                $this->_helper->json(['success' => true]);
            }else{
                throw new \Exception("CallbackSetting whith the id '" .$this->getParam('id')."' doesn't exist.");
            }
        }catch (\Exception $e){
            $this->_helper->json(['success' => false,'message' => $e->getMessage()]);
        }
    }

    public function listAction(){

        $list = new CallbackSetting\Listing();
        $list->setOrder('DESC');
        $list->setOrderKey('id');
        $list->setLimit($this->getParam('limit',25));
        $list->setOffset($this->getParam("start"));
        if($filterCondition = \Pimcore\Admin\Helper\QueryParams::getFilterCondition($this->getParam('filter'))){
            $list->setCondition($filterCondition);
        }
        if($type = $this->getParam('type')){
            $list->setCondition(' `type` = ?',[$type]);
        }

        foreach($list->load() as $item){
            $tmp = $item->getObjectVars();
            $tmp['extJsSettings'] = \Zend_Json::decode($tmp['settings']);
            $data[] = $tmp;
        }
        
        $this->_helper->json(['total' => $list->getTotalCount(),'success' => true,'data' => $data]);
    }
}

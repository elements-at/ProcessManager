<?php

class ProcessManager_RestController extends \Pimcore\Controller\Action\Webservice
{
    /**
     * @var \Pimcore\Model\User
     */
    protected $user ;

    public function init(){
        parent::init();
        $this->user = \Zend_Registry::get("pimcore_admin_user");
    }

    protected function getUser(){
        return $this->user;
    }

    public function executeAction()
    {

        if(!$this->user->isAllowed('plugin_pm_permission_execute') || !$this->user->isAllowed('plugin_pm_permission_view')){
            $this->_helper->json(['success' => false,'The current user is not allowed to execute or view the processes.']);
        }

        if(!$this->getParam('id') && !$this->getParam('name')){
            $this->_helper->json(['success' => false,'Please provide a "name" or "id" parameter/value.']);
        }

        $list = new ProcessManager\Configuration\Listing();
        $list->setUser($this->getUser());
        if($id = $this->getParam('id')){
            $list->setCondition('id = ?',[$id]);
        }elseif($name = $this->getParam('name')){
            $list->setCondition('name = ?',[$name]);
        }
        $config = $list->load()[0];
        if(!$config){
            $this->_helper->json(['success' => false,'message' => "Couldn't find a process to execute."]);
        }

        $callbackSettings = [];

        if($val = $this->getParam('callbackSettings')){
            $callbackSettings = json_decode($val,true);
            if(!is_array($callbackSettings)){
                $xml = @simplexml_load_string($val);
                if($xml !== false){
                    $callbackSettings = json_decode(json_encode($xml),true);
                }
            }

            if($val && !$callbackSettings){
                $this->_helper->json(['success' => false,'message' => "Couldn't decode the callbackSettigs. Please make sure that you passed a valid JSON or XML."]);
            }
        }

        $result = \ProcessManager\Helper::executeJob($this->getParam('id'),$callbackSettings,$this->getUser()->getId());
        unset($result['executedCommand']);
        $this->_helper->json($result);
    }

    public function monitoringItemStateAction(){
        $list = new ProcessManager\MonitoringItem\Listing();
        $list->setUser($this->getUser());

        if(!$this->user->isAllowed('plugin_pm_permission_execute') || !$this->user->isAllowed('plugin_pm_permission_view')){
            $this->_helper->json(['success' => false,'The current user is not allowed to execute or view the processes.']);
        }

        $list->setCondition(' id = ?',[$this->getParam('id')]);

        $monitoringItem = $list->load()[0];
        if(!$monitoringItem){
            $this->_helper->json(['success' => false,'message' => 'The monitoring Item was not found.']);
        }
        $monitoringItem->getLogger()->notice('Checked by rest webservice User ID: ' . $this->getUser()->getId());
        $this->_helper->json(['success' => true, 'data' => $monitoringItem->getForWebserviceExport()]);
    }

    public function testAction(){

        $this->testJson = '
            {
                "firstName" : "christian",
                "lastName" : "kogler"
            }
        ';

        $this->testXML = '';


        if($this->getRequest()->isPost()){


            $url = \Pimcore\Tool::getHostUrl().'/plugin/ProcessManager/rest/execute?id=' . $this->getParam('id').'&apikey=' . $this->getParam('apikey');
            $client = \Pimcore\Tool::getHttpClient();
            $client->setUri($url);
            $params = [
                'id' => $this->getParam('id'),
                'name' => $this->getParam('name'),
                'callbackSettings' => $this->getParam('callbackSettings')
            ];
            $client->setParameterPost($params);
            $result = $client->request($client::POST)->getBody();
            $this->view->result = $result;
        }

        $configs = new \ProcessManager\Configuration\Listing();

        $options = [];
        foreach ($configs->load() as $config){
            $options[$config->getId()] = $config->getId().' - ' . $config->getName();
        }
        $this->view->options = $options;

    }

}
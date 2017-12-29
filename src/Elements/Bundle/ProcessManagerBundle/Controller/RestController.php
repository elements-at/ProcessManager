<?php

namespace Elements\Bundle\ProcessManagerBundle\Controller;

use Elements\Bundle\ProcessManagerBundle\Helper;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Bundle\AdminBundle\Controller\Rest\AbstractRestController;
use Pimcore\Templating\Model\ViewModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/webservice/elementsprocessmanager/rest")
 */
class RestController extends AbstractRestController
{

    /**
     * @Route("/execute")
     * @param Request $request
     * @return JsonResponse
     */
    public function executeAction(Request $request)
    {

        if (!$this->checkPermission('plugin_pm_permission_execute') || !$this->checkPermission('plugin_pm_permission_view')) {
            return $this->adminJson(['success' => false, 'The current user is not allowed to execute or view the processes.']);
        }

        if (!$request->get('id') && !$request->get('name')) {
            return $this->adminJson(['success' => false, 'Please provide a "name" or "id" parameter/value.']);
        }

        $list = new Configuration\Listing();
        $list->setUser($this->getAdminUser());
        if ($id = $request->get('id')) {
            $list->setCondition('id = ?', [$id]);
        } elseif ($name = $request->get('name')) {
            $list->setCondition('name = ?', [$name]);
        }
        $config = $list->load()[0];
        if (!$config) {
            return $this->adminJson(['success' => false, 'message' => "Couldn't find a process to execute."]);
        }

        $callbackSettings = [];

        if ($val = $request->get('callbackSettings')) {
            $callbackSettings = json_decode($val, true);
            if (!is_array($callbackSettings)) {
                $xml = @simplexml_load_string($val);
                if ($xml !== false) {
                    $callbackSettings = json_decode(json_encode($xml), true);
                }
            }

            if ($val && !$callbackSettings) {
                return $this->adminJson(['success' => false, 'message' => "Couldn't decode the callbackSettigs. Please make sure that you passed a valid JSON or XML."]);
            }
        }

        $result = Helper::executeJob($request->get('id'), $callbackSettings, $this->getAdminUser()->getId());
        unset($result['executedCommand']);
        return $this->adminJson($result);
    }

    /**
     * @Route("/monitoring-item-state")
     * @param Request $request
     * @return JsonResponse
     */
    public function monitoringItemStateAction(Request $request)
    {
        $list = new MonitoringItem\Listing();
        $list->setUser($this->getAdminUser());

        if (!$this->checkPermission('plugin_pm_permission_execute') || !$this->checkPermission('plugin_pm_permission_view')) {
            return $this->adminJson(['success' => false, 'The current user is not allowed to execute or view the processes.']);
        }

        $list->setCondition(' id = ?', [$request->get('id')]);

        $monitoringItem = $list->load()[0];
        if (!$monitoringItem) {
            return $this->adminJson(['success' => false, 'message' => 'The monitoring Item was not found.']);
        }
        $monitoringItem->getLogger()->notice('Checked by rest webservice User ID: ' . $this->getAdminUser()->getId());
        return $this->adminJson(['success' => true, 'data' => $monitoringItem->getForWebserviceExport()]);
    }

    /**
     * @Route("/test")
     * @param Request $request
     * @return ViewModel
     */
    public function testAction(Request $request)
    {

        $this->testJson = '
            {
                "firstName" : "christian",
                "lastName" : "kogler"
            }
        ';

        $this->testXML = '';

        $viewData = array();


        if ($this->getRequest()->isPost()) {


            $url = \Pimcore\Tool::getHostUrl() . '/webservice/elementsprocessmanager/rest/execute?id=' . $request->get('id') . '&apikey=' . $request->get('apikey');
            $client = \Pimcore\Tool::getHttpClient();
            $client->setUri($url);
            $params = [
                'id' => $request->get('id'),
                'name' => $request->get('name'),
                'callbackSettings' => $request->get('callbackSettings')
            ];
            $client->setParameterPost($params);
            $result = $client->request($client::POST)->getBody();
            $viewData["result"] = $result;
        }

        $configs = new Configuration\Listing();

        $options = [];
        foreach ($configs->load() as $config) {
            $options[$config->getId()] = $config->getId() . ' - ' . $config->getName();
        }
        $viewData["options"] = $options;
        return new ViewModel($viewData);

    }

}

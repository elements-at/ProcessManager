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

namespace Elements\Bundle\ProcessManagerBundle\Controller;

use Elements\Bundle\ProcessManagerBundle\Helper;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Bundle\AdminBundle\Controller\Rest\AbstractRestController;
use Pimcore\Controller\FrontendController;
use Pimcore\Templating\Model\ViewModel;
use Pimcore\Tool\Frontend;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Elements\Bundle\ProcessManagerBundle\Enums;
/**
 * @Route("/webservice/elementsprocessmanager/rest")
 */
class RestController extends FrontendController
{

    protected function getApiUser(Request $request){
        $user = \Pimcore\Model\User::getByName($request->get('username'));
        if(!$user){
            return $this->json(['success' => false, 'message' => 'User not found']);
        }

        $config = \Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle::getConfiguration();
        $validApiUser = false;

        foreach($config->getRestApiUsers() as $entry){
            if($entry['username'] == $user->getName()){
                if($request->get('apiKey') == $entry['apiKey']){
                    $validApiUser = true;
                }else{
                    return $this->json(['success' => false, 'message' => 'No valid api key for user']);
                }
            }
        }
        if($validApiUser == false){
            return $this->json(['success' => false, 'message' => 'The user is not a valid api user']);
        }
        if(!$user->getPermission(Enums\Permissions::EXECUTE) || !$user->getPermission(Enums\Permissions::VIEW)){
            return $this->json(['success' => false, 'message' => 'Missing permissions for user']);
        }

        return $user;
    }
    /**
     * @Route("/execute")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function executeAction(Request $request)
    {
        $user = $this->getApiUser($request);
        if($user instanceof \Pimcore\Model\User == false){
            return $user;
        }

        if (!$request->get('id') && !$request->get('name')) {
            return $this->json(['success' => false, 'message' => 'Please provide a "name" or "id" parameter/value.']);
        }

        $list = new Configuration\Listing();
        $list->setUser($user);
        if ($id = $request->get('id')) {
            $list->setCondition('id = ?', [$id]);
        } elseif ($name = $request->get('name')) {
            $list->setCondition('name = ?', [$name]);
        }
        $config = $list->load()[0];
        if (!$config) {
            return $this->json(['success' => false, 'message' => "Couldn't find a process to execute."]);
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
                return $this->json(['success' => false, 'message' => "Couldn't decode the callbackSettigs. Please make sure that you passed a valid JSON or XML."]);
            }
        }

        $result = Helper::executeJob($config->getId(), $callbackSettings, $user->getId());
        unset($result['executedCommand']);

        return $this->json($result);
    }

    /**
     * @Route("/monitoring-item-state")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function monitoringItemStateAction(Request $request)
    {
        $user = $this->getApiUser($request);
        if($user instanceof \Pimcore\Model\User == false){
            return $user;
        }

        $list = new MonitoringItem\Listing();
        $list->setUser($user);

        $list->setCondition(' id = ?', [$request->get('id')]);

        $monitoringItem = $list->current();
        if (!$monitoringItem) {
            return $this->json(['success' => false, 'message' => 'The monitoring Item was not found.']);
        }
        $monitoringItem->getLogger()->notice('Checked by rest webservice User ID: ' . $user->getId());

        return $this->json(['success' => true, 'data' => $monitoringItem->getForWebserviceExport()]);
    }
}

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

use Elements\Bundle\ProcessManagerBundle\Executor\AbstractExecutor;
use Elements\Bundle\ProcessManagerBundle\Executor\Action\AbstractAction;
use Elements\Bundle\ProcessManagerBundle\Helper;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Bundle\AdminBundle\Helper\QueryParams;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/elementsprocessmanager/config")
 */
class ConfigController extends AdminController
{
    /**
     * @Route("/get-by-id")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getByIdAction(Request $request)
    {
        try {
            $list = new Configuration\Listing();
            $list->setUser($this->getAdminUser())->setCondition('id = ?', [$request->get('id')]);
            $config = $list->load()[0];

            $values = $config->getObjectVars();
            if ($tmp = $values['executorSettings']) {
                $values['executorSettings'] = json_decode($tmp, true);
            }
            $result = [
                'success' => true,
                'data' => $values
            ];
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        return $this->adminJson($result);
    }

    /**
     * @Route("/list")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $this->checkPermission('plugin_pm_permission_view');
        $data = [];
        $list = new Configuration\Listing();
        $list->setOrder('ASC');
        $list->setOrderKey('name');
        $list->setLimit($request->get('limit', 25));
        $list->setOffset($request->get('start'));

        $sortingSettings = QueryParams::extractSortingSettings($request->request->all());
        if ($sortingSettings['orderKey'] && $sortingSettings['order']) {
            $list->setOrderKey($sortingSettings['orderKey']);
            $list->setOrder($sortingSettings['order']);
        }

        $list->setUser($this->getAdminUser());

        if ($filterCondition = QueryParams::getFilterCondition($request->get('filter'))) {
            $list->setCondition($filterCondition);
        }

        foreach ($list->load() as $item) {
            $tmp = $item->getObjectVars();

            $tmp['command'] = $item->getCommand();
            $executorClassObject = $item->getExecutorClassObject();
            $tmp['type'] = $executorClassObject->getName();
            $tmp['extJsSettings'] = $executorClassObject->getExtJsSettings();

            $tmp['active'] = (int)$tmp['active'];
            try {
                if ($item->getCronJob()) {
                    $nextRunTs = $item->getNextCronJobExecutionTimestamp();
                    if ($nextRunTs) {
                        $tmp['cronJob'] .= ' <br/>(Next run:' . date('Y-m-d H:i:s', $nextRunTs) . ')';
                    }
                }
            } catch (\Exception $e) {
                $tmp['cronJob'] = $e->getMessage();
            }
            $data[] = $tmp;
        }

        return $this->adminJson(['total' => $list->getTotalCount(), 'success' => true, 'data' => $data]);
    }

    /**
     * @Route("/save")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function saveAction(Request $request)
    {
        $this->checkPermission('plugin_pm_permission_configure');

        $data = json_decode($request->get('data'), true);

        $values = $data['values'];
        $executorConfig = $data['executorConfig'];

        $actions = $data['actions'];
        /**
         * @var $executorClass AbstractExecutor
         * @var $configuration Configuration
         */
        $executorClass = new $executorConfig['class']();
        $executorClass->setValues($data['values']);

        $actions = [];

        foreach($data['actions'] as $actionData){
            /**
             * @var $obj AbstractAction
             */
            $className = $actionData['class'];
            $obj = new $className();
            $obj->setValues($actionData);
            $actions[] = $obj;
        }
        $executorClass->setActions($actions);
        $executorClass->setLoggers($data['loggers']);

        // $executorClass->setValues($values)->setExecutorConfig($executorConfig)->setActions($actions);
        if (!$request->get('id')) {
            $configuration = new Configuration();
            $configuration->setActive(true);
        } else {
            $configuration = Configuration::getById($request->get('id'));
        }
        foreach ($values as $key => $v) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($configuration, $setter)) {
                $configuration->$setter(trim($v));
            }
        }
        $configuration->setExecutorClass($executorConfig['class']);
        $configuration->setExecutorSettings($executorClass->getStorageValue());
        try {
            $configuration->save();
        } catch (\Exception $e) {
            return $this->adminJson(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->adminJson(['success' => true, 'id' => $configuration->getId()]);
    }

    /**
     * @Route("/delete")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $this->checkPermission('plugin_pm_permission_configure');

        $config = Configuration::getById($request->get('id'));
        if ($config instanceof Configuration) {
            $config->delete();
        }

        return $this->adminJson(['success' => true]);
    }

    /**
     * @Route("/activate-disable")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function activateDisableAction(Request $request)
    {
        try {
            $config = Configuration::getById($request->get('id'));
            $config->setActive((int)$request->get('value'))->save();

            return $this->adminJson(['success' => true]);
        } catch (\Exception $e) {
            return $this->adminJson(['success' => false, 'message' => $e->getMessage()]);
        }
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
        $this->checkPermission('plugin_pm_permission_execute');
        $callbackSettings = $request->get('callbackSettings') ? json_decode($request->get('callbackSettings'), true) : [];
        $result = Helper::executeJob($request->get('id'), $callbackSettings, $this->getAdminUser()->getId());

        return $this->adminJson($result);
    }
}

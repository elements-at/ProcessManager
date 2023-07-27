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
 * @copyright  Copyright (c) elements.at New Media Solutions GmbH (https://www.elements.at)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Elements\Bundle\ProcessManagerBundle\Controller;

use Elements\Bundle\ProcessManagerBundle\Enums;
use Elements\Bundle\ProcessManagerBundle\Executor\AbstractExecutor;
use Elements\Bundle\ProcessManagerBundle\Executor\Action\AbstractAction;
use Elements\Bundle\ProcessManagerBundle\Helper;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Service\UploadManger;
use Pimcore\Bundle\AdminBundle\Helper\QueryParams;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/elementsprocessmanager/config')]
class ConfigController extends UserAwareController
{
    use JsonHelperTrait;

    #[Route(path: '/get-by-id')]
    public function getByIdAction(Request $request): JsonResponse
    {
        try {
            $list = new Configuration\Listing();
            $list->setUser($this->getPimcoreUser())->setCondition('id = ?', [$request->get('id')]);
            $config = $list->load()[0];

            $values = $config->getObjectVars();
            if ($tmp = $values['executorSettings']) {
                $values['executorSettings'] = json_decode((string)$tmp, true, 512, JSON_THROW_ON_ERROR);
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

        return $this->jsonResponse($result);
    }

    #[Route(path: '/list')]
    public function listAction(Request $request): JsonResponse
    {
        $this->checkPermission(Enums\Permissions::VIEW);
        $data = [];
        $list = new Configuration\Listing();
        $list->setOrder('ASC');
        $list->setOrderKey('name');
        $list->setLimit($request->get('limit', 25));
        $list->setOffset($request->get('start', 0));

        $sortingSettings = QueryParams::extractSortingSettings($request->request->all());
        if ($sortingSettings['orderKey'] && $sortingSettings['order']) {
            $list->setOrderKey($sortingSettings['orderKey']);
            $list->setOrder($sortingSettings['order']);
        }

        $list->setUser($this->getPimcoreUser());

        if ($filterCondition = QueryParams::getFilterCondition($request->get('filter', ''), [])) {
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

        return $this->jsonResponse(['total' => $list->getTotalCount(), 'success' => true, 'data' => $data]);
    }

    #[Route(path: '/save', methods: ['POST'])]
    public function saveAction(Request $request): JsonResponse
    {
        $this->checkPermission(Enums\Permissions::CONFIGURE);

        $data = json_decode((string)$request->get('data'), true, 512, JSON_THROW_ON_ERROR);

        $values = $data['values'];
        $executorConfig = $data['executorConfig'];

        $actions = $data['actions'];
        /**
         * @var AbstractExecutor $executorClass
         */
        $executorClass = new $executorConfig['class']();
        $executorClass->setValues($data['values']);

        $actions = [];

        foreach ($data['actions'] as $actionData) {
            /**
             * @var AbstractAction $obj
             */
            $className = $actionData['class'];
            $obj = new $className();
            $obj->setValues($actionData);
            $actions[] = $obj;
        }
        $executorClass->setActions($actions);
        $executorClass->setLoggers($data['loggers']);

        // $executorClass->setValues($values)->setExecutorConfig($executorConfig)->setActions($actions);
        $request_configuration = $request->request->get('id');
        $configuration = Configuration::getById($request->get('id'));

        if ($request_configuration == '') { // Does the id exist?
            $configuration = new Configuration();
            $configuration->setActive(true);
        }
        if ($configuration->getId() != $request_configuration && Configuration::getById($request_configuration) != null) { // Is there an update call on an already used id?
            throw new \Exception('Cannot create or update command, the chosen id already exists!');
        }

        foreach ($values as $key => $v) {
            $setter = 'set' . ucfirst((string)$key);
            if (method_exists($configuration, $setter)) {
                $configuration->$setter(trim((string)$v));
            }
        }
        $configuration->setExecutorClass($executorConfig['class']);
        $configuration->setExecutorSettings($executorClass->getStorageValue());

        try {
            $configuration->save(['oldId' => $request_configuration]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->jsonResponse(['success' => true, 'id' => $configuration->getId()]);
    }

    #[Route(path: '/delete')]
    public function deleteAction(Request $request): JsonResponse
    {
        $this->checkPermission(Enums\Permissions::CONFIGURE);

        $config = Configuration::getById($request->get('id'));
        if ($config instanceof Configuration) {
            $config->delete();
        }

        return $this->jsonResponse(['success' => true]);
    }

    #[Route(path: '/activate-disable')]
    public function activateDisableAction(Request $request): JsonResponse
    {
        try {
            $config = Configuration::getById($request->get('id'));
            $config->setActive((int)$request->get('value'))->save();

            return $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    #[Route(path: '/execute')]
    public function executeAction(Request $request, UploadManger $uploadManger): JsonResponse
    {
        $this->checkPermission(Enums\Permissions::EXECUTE);
        $callbackSettings = $request->get('callbackSettings') ? json_decode((string)$request->get('callbackSettings'), true, 512, JSON_THROW_ON_ERROR) : [];

        $result = Helper::executeJob(
            $request->get('id'),
            $callbackSettings,
            $this->getPimcoreUser()->getId(),
            [],
            null,
            function ($monitoringItem, $executor) use ($request, $uploadManger) {
                $uploadManger->saveUploads($request, $monitoringItem);
            }
        );

        return $this->jsonResponse($result);
    }
}

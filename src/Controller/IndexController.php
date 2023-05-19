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

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Enums;
use Elements\Bundle\ProcessManagerBundle\Executor\Action\AbstractAction;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Elements\Bundle\ProcessManagerBundle\Service\CommandsValidator;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\GridConfig;
use Pimcore\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/elementsprocessmanager/index")
 */
class IndexController extends AdminController
{
    /**
     * @Route("/get-plugin-config")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getPluginConfigAction(Request $request, CommandsValidator $commandsValidator, Translator $translator)
    {
        $this->checkPermission(Enums\Permissions::VIEW);

        $bundleConfig = ElementsProcessManagerBundle::getConfiguration();
        $data = $bundleConfig->getClassTypes();

        $commands = (array)$commandsValidator->getValidCommands();
        foreach ($commands as $key => $command) {
            $tmp = ['description' => $command->getDescription(), 'options' => $command->getDefinition()->getOptions()];
            $commands[$key] = $tmp;
        }

        $data['pimcoreCommands'] = $commands;

        $data['roles'] = [];

        $list = new \Pimcore\Model\User\Role\Listing();
        $list->setOrder('ASC')->setOrderKey('name');
        foreach ($list->load() as $role) {
            $data['roles'][] = [
                'id' => $role->getId(),
                'name' => $role->getName(),
            ];
        }

        $data['permissions'] = [];
        $list = new \Pimcore\Model\User\Permission\Definition\Listing();
        $list->setOrder('ASC')->setOrderKey('key');
        foreach ($list->load() as $permission) {
            $data['permissions'][] = [
                'key' => $permission->getKey(),
                'name' => $translator->trans($permission->getKey(), [], 'admin'). ' (' . $permission->getKey().')',
                'category' => $permission->getCategory()
            ];
        }

        usort($data['permissions'], function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        $shortCutMenu = [];

        if($bundleConfig->getDisableShortcutMenu() == false) {
            $list = new Configuration\Listing();
            $list->setUser($this->getAdminUser());
            $list->setOrderKey('name');
            foreach ($list->load() as $config) {
                $group = $config->getGroup() ?: 'default';
                $shortCutMenu[$group][] = [
                    'id' => $config->getId(),
                    'name' => $config->getName(),
                    'group' => $config->getGroup(),
                ];
            }
            $data['shortCutMenu'] = $shortCutMenu ?: false;
        }

        if($data['shortCutMenu'] ?? null) {
            ksort($data['shortCutMenu'], SORT_LOCALE_STRING);
        }

        return $this->adminJson($data);
    }

    /**
     * @Route("/download")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function downloadAction(Request $request)
    {
        $monitoringItem = MonitoringItem::getById($request->get('id'));
        $actions = $monitoringItem->getActions();
        foreach ($actions as $action) {
            if ($action['accessKey'] == $request->get('accessKey')) {
                $className = $action['class'];
                /**
                 * @var $class AbstractAction
                 */
                $class = new $className();
                $result = $class->execute($monitoringItem, $action);

                return $result;
            }
        }
    }

    /**
     * @Route("/update-plugin")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updatePluginAction(Request $request)
    {
        //just for testing

        $method = 'updateVersion'.$request->get('version');

        Updater::getInstance()->$method();
        die();
    }

    /**
     * @Route("/property-list")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function propertyListAction(Request $request)
    {
        $result = [];
        $fieldName = $request->get('fieldName');

        if ($fieldName == 'myProperties') {
            $result = [];
            for ($i = 1; $i < 50; $i++) {
                $result[] = ['id' => $i, 'name' => 'Display text - '.$fieldName.' - '.$i];
            }
        }

        return $this->adminJson(['success' => true, 'data' => $result]);
    }

    /**
     * @Route("/get-classes")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Exception
     *
     */
    public function getClassesAction(Request $request): JsonResponse
    {
        $result = [];

        $list = new ClassDefinition\Listing();
        $list->setOrderKey('name')->setOrder('ASC');
        foreach ($list as $c) {
            $result[] = ['id' => $c->getId(), 'name' => $c->getName()];
        }

        return new JsonResponse(['data' => $result]);
    }

    /**
     * @Route("/get-grid-configs")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Exception
     *
     */
    public function getGridConfigsAction(Request $request): JsonResponse
    {
        $result = [];

        $list = new GridConfig\Listing();
        $list->setOrderKey('name');
        $list->setCondition('ownerId = ? OR shareGlobally =1', [$this->getAdminUser()->getId()]);
        $config = $list->load();
        foreach ($list as $c) {
            $result[] = ['id' => $c->getId(), 'name' => $c->getName()];
        }

        return new JsonResponse(['data' => $result]);
    }
}

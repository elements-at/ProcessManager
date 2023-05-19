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

use Elements\Bundle\ProcessManagerBundle\Model\CallbackSetting;
use Pimcore\Bundle\AdminBundle\Helper\QueryParams;
use Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/elementsprocessmanager/callback-settings')]
class CallbackSettingsController extends UserAwareController
{
    use JsonHelperTrait;

    /**
     * @return JsonResponse
     */
    #[Route(path: '/save')]
    public function saveAction(Request $request)
    {
        try {
            $values = json_decode((string) $request->get('values'), true, 512, JSON_THROW_ON_ERROR);
            $settings = json_decode((string) $request->get('settings'), true, 512, JSON_THROW_ON_ERROR);
            if ($request->get('id')) {
                $setting = CallbackSetting::getById($request->get('id'));
            } else {
                $setting = new CallbackSetting();
            }

            $setting = $setting->setName($values['name'])
                ->setDescription($values['description'])
                ->setType($request->get('type'))
                ->setSettings($request->get('settings'))->save();

            return $this->jsonResponse(['success' => true, 'id' => $setting->getId()]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/delete')]
    public function deleteAction(Request $request)
    {
        try {
            $setting = CallbackSetting::getById($request->get('id'));
            $setting->delete();

            return $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/copy')]
    public function copyAction(Request $request)
    {
        try {
            $setting = CallbackSetting::getById($request->get('id'));
            if ($setting) {
                $setting->setId(null)->setName('Copy - ' . $setting->getName())->save();

                return $this->jsonResponse(['success' => true]);
            } else {
                throw new \Exception("CallbackSetting whith the id '" . $request->get('id') . "' doesn't exist.");
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/list')]
    public function listAction(Request $request)
    {
        $list = new CallbackSetting\Listing();
        $list->setOrder('DESC');
        $list->setOrderKey('id');
        $list->setLimit($request->get('limit', 25));
        $list->setOffset($request->get('start'));
        if ($filterCondition = QueryParams::getFilterCondition($request->get('filter'))) {
            $list->setCondition($filterCondition);
        }

        if ($id = $request->get('id')) {
            $list->setCondition(' `id` = ?', [$id]);
        } else {
            if ($type = $request->get('type')) {
                $list->setCondition(' `type` = ?', [$type]);
            }
        }

        $data = [];
        foreach ($list->load() as $item) {
            $tmp = $item->getObjectVars();
            $tmp['extJsSettings'] = json_decode((string) $tmp['settings'], true, 512, JSON_THROW_ON_ERROR);
            $data[] = $tmp;
        }

        return $this->jsonResponse(['total' => $list->getTotalCount(), 'success' => true, 'data' => $data]);
    }
}

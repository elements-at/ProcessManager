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

use Elements\Bundle\ProcessManagerBundle\Executor\Logger\File;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\DataObject\AbstractObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/elementsprocessmanager/test")
 */
class TestController extends AdminController
{
    /**
     * @Route("/test")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function testAction(Request $request)
    {
        $o = AbstractObject::getById(49);
        $o->setCbx('0');
        $o->save();
        die('done ' . date('c'));

        $result = $this->render('ElementsProcessManagerBundle::report-email.html.php', [
            'reportItems' => []
        ]);
        var_dump($result);
        die();
    }
}

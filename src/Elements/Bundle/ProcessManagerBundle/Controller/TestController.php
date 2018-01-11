<?php

namespace Elements\Bundle\ProcessManagerBundle\Controller;

use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Executor\Action\AbstractAction;
use Elements\Bundle\ProcessManagerBundle\Executor\Logger\AbstractLogger;
use Elements\Bundle\ProcessManagerBundle\Executor\Logger\Application;
use Elements\Bundle\ProcessManagerBundle\Executor\Logger\File;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Templating\Model\ViewModel;
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
     * @param Request $request
     * @return JsonResponse
     */
    public function testAction(Request $request)
    {
        $o = AbstractObject::getById(49);
        $o->setCbx("0");
        $o->save();
        die("done " . date('c'));


        $result = $this->render('ElementsProcessManagerBundle::report-email.html.php', array(
            "reportItems" => array()
        ));
        var_dump($result);
        die();

    }
}

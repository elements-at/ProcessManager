<?php

namespace Elements\Bundle\ProcessManagerBundle;

use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Event\SystemEvents;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Templating\EngineInterface;

class SystemEventsListener implements EventSubscriberInterface
{

    /**
     * @var EngineInterface
     */
    protected $renderingEngine;

    /**
     * SystemEventsListener constructor.
     * @param EngineInterface $renderingEngine
     */
    public function __construct(EngineInterface $renderingEngine)
    {
        $this->renderingEngine = $renderingEngine;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SystemEvents::MAINTENANCE => 'onMaintenance',
            ConsoleEvents::ERROR => 'onConsoleError',
            ConsoleEvents::TERMINATE => 'onConsoleTerminate',


        ];
    }


    /**
     * @param ConsoleErrorEvent $e
     */
    public function onConsoleError(ConsoleErrorEvent $e)
    {
        if(!ElementsProcessManagerBundle::isInstalled()) {
            return;
        }

        if ($monitoringItem = ElementsProcessManagerBundle::getMonitoringItem()) {
            $error = $e->getError();
            $monitoringItem->setMessage('ERROR:' . $error . $monitoringItem->getMessage());
            $monitoringItem->setPid(null)->setStatus($monitoringItem::STATUS_FAILED)->save();
        }
    }

    /**
     * @param ConsoleTerminateEvent $e
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $e)
    {
        if(!ElementsProcessManagerBundle::isInstalled()) {
            return;
        }

        if ($e->getExitCode() == 0) {
            if ($monitoringItem = ElementsProcessManagerBundle::getMonitoringItem()) {
                if ($config = Configuration::getById($monitoringItem->getConfigurationId())) {
                    $versions = $config->getKeepVersions();
                    if (is_numeric($versions)) {
                        $list = new MonitoringItem\Listing();
                        $list->setOrder('DESC')->setOrderKey('id')->setOffset((int)$versions)->setLimit(
                            100000000000
                        ); //a limit has to defined otherwise the offset wont work
                        $list->setCondition(
                            'status ="finished" AND configurationId=? AND IFNULL(pid,0) != ? ',
                            [$config->getId(), $monitoringItem->getPid()]
                        );

                        $items = $list->load();
                        foreach ($items as $item) {
                            $item->delete();
                        }
                    }
                }
                if (!$monitoringItem->getMessage()) {
                    $monitoringItem->setMessage('finished');
                }
                $monitoringItem->setCompleted();
                $monitoringItem->setPid(null)->save();
            }
        }
    }


    /**
     *
     */
    public function onMaintenance()
    {
        if(!ElementsProcessManagerBundle::isInstalled()) {
            return;
        }

        $config = ElementsProcessManagerBundle::getConfig();
        if ($config['general']['executeWithMaintenance']) {
            ElementsProcessManagerBundle::initProcessManager(
                null,
                ElementsProcessManagerBundle::$maintenanceOptions
            );
            $maintenance = new \Elements\Bundle\ProcessManagerBundle\Maintenance($this->renderingEngine);
            $maintenance->execute();
        }
    }
}

<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle;

use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SystemEventsListener implements EventSubscriberInterface
{
    public function __construct(private readonly Installer $installer)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::ERROR => 'onConsoleError',
            ConsoleEvents::TERMINATE => 'onConsoleTerminate',

        ];
    }

    public function onConsoleError(ConsoleErrorEvent $e): void
    {
        if (!\Pimcore::isInstalled() || !$this->installer->isInstalled()) {
            return;
        }

        if (($monitoringItem = ElementsProcessManagerBundle::getMonitoringItem()) instanceof \Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem) {
            $error = $e->getError();
            $monitoringItem->setMessage('ERROR: ' . $error->getMessage());
            $monitoringItem->getLogger()->error($error->getMessage());
            $monitoringItem->setPid(null)->setStatus($monitoringItem::STATUS_FAILED)->save();
        }
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $e): void
    {
        if (!\Pimcore::isInstalled() || !$this->installer->isInstalled()) {
            return;
        }

        if (($monitoringItem = ElementsProcessManagerBundle::getMonitoringItem()) instanceof \Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem) {
            Helper::executeMonitoringItemLoggerShutdown($monitoringItem);
        }

        if ($e->getExitCode() == 0 && ($monitoringItem = ElementsProcessManagerBundle::getMonitoringItem())) {
            if (($config = Configuration::getById($monitoringItem->getConfigurationId() ?? '')) instanceof \Elements\Bundle\ProcessManagerBundle\Model\Configuration) {
                $versions = $config->getKeepVersions();
                if (is_numeric($versions)) {
                    $list = new MonitoringItem\Listing();
                    $list->setOrder('DESC')->setOrderKey('id')->setOffset((int)$versions)->setLimit(
                        100_000_000_000
                    ); //a limit has to defined otherwise the offset won't work
                    $list->setCondition(
                        'status ="finished" AND configurationId=? AND IFNULL(pid,0) != ? AND parentId IS NULL ',
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

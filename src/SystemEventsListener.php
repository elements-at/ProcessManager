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

namespace Elements\Bundle\ProcessManagerBundle;

use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SystemEventsListener implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::ERROR => 'onConsoleError',
            ConsoleEvents::TERMINATE => 'onConsoleTerminate',

        ];
    }

    /**
     * @param ConsoleErrorEvent $e
     */
    public function onConsoleError(ConsoleErrorEvent $e)
    {
        if (!\Pimcore::isInstalled() || !ElementsProcessManagerBundle::isInstalled()) {
            return;
        }

        if ($monitoringItem = ElementsProcessManagerBundle::getMonitoringItem()) {
            $error = $e->getError();
            $monitoringItem->setMessage('ERROR: ' . $error->getMessage());
            $monitoringItem->getLogger()->error($error);
            $monitoringItem->setPid(null)->setStatus($monitoringItem::STATUS_FAILED)->save();
        }
    }

    /**
     * @param ConsoleTerminateEvent $e
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $e)
    {
        if (!\Pimcore::isInstalled() || !ElementsProcessManagerBundle::isInstalled()) {
            return;
        }

        if ($monitoringItem = ElementsProcessManagerBundle::getMonitoringItem()) {
            Helper::executeMonitoringItemLoggerShutdown($monitoringItem);
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
}

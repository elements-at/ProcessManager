<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\MessageHandler;

use Elements\Bundle\ProcessManagerBundle\Message\StopProcessMessage;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Process\Process;

#[AsMessageHandler]
class StopProcessHandler
{
    public function __invoke(StopProcessMessage $message): void
    {
        if($monitoringItem = MonitoringItem::getById($message->getMonitoringItemId())) {
            if(!$pid = $monitoringItem->getPid()) {
                return;
            }

            $monitoringItem->setPid(null)->setStatus(MonitoringItem::STATUS_FAILED)->save();
            $process = Process::fromShellCommandline('kill -9 '.$pid);
            $process->run();
        }
    }
}

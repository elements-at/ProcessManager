<?php

namespace Elements\Bundle\ProcessManagerBundle\MessageHandler;

use Elements\Bundle\ProcessManagerBundle\Message\ExecuteCommandMessage;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Tool\Console;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExecuteCommandHandler
{
    public function __invoke(ExecuteCommandMessage $message)
    {
        $pid = Console::execInBackground($message->getCommand(), $message->getOutputFile());
        if($monitoringItem = MonitoringItem::getById($message->getMonitoringItemId())){
            $monitoringItem
                ->setMessengerPending(false)
                ->setPid($pid)
                ->save();

            $monitoringItem->getLogger()->info('Execution Command: ' . $message->getCommand() . ' in Background');
        }
    }
}

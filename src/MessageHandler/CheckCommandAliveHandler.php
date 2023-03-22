<?php

namespace Elements\Bundle\ProcessManagerBundle\MessageHandler;

use Elements\Bundle\ProcessManagerBundle\Message\CheckCommandAliveMessage;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Tool\Console;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CheckCommandAliveHandler
{
    public function __invoke(CheckCommandAliveMessage $message)
    {

        if($monitoringItem = MonitoringItem::getById($message->getMonitoringItemId())){
            if(!$pid = $monitoringItem->getPid()) {
                return null;
            }


            $checks = 0;
            while(in_array(MonitoringItem::getById($monitoringItem->getId())->getStatus(),[MonitoringItem::STATUS_INITIALIZING,MonitoringItem::STATUS_UNKNOWN])){ //check for state because shortly after the background execution the process is alive...
                $this->checkPid($monitoringItem);
                usleep(500000);
                $checks++;
                if($checks > 3){
                    break; //just to make sure we do not end in a endlessloop
                }
            }

            $this->checkPid($monitoringItem);
        }
    }


    protected function checkPid(MonitoringItem $monitoringItem)
    {
        if(!$pid = $monitoringItem->getPid()) {
            return null;
        }

        if(!$this->pidExists($pid)){
            $monitoringItem->setPid(null)->getLogger()->debug('PID' . $pid.' does not exist - removing pid');
            $monitoringItem->save();
        }
    }

    protected function pidExists($pid) : bool {
        if(function_exists("posix_getpgid")){
            return posix_getpgid($pid);
        }else{
            return file_exists('/proc/'.$pid);
        }
    }
}

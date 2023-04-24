<?php

namespace Elements\Bundle\ProcessManagerBundle\Message;

class CheckCommandAliveMessage
{
    public function __construct(
        private int $monitoringItemId,
    ){
    }

    public function getMonitoringItemId(): int
    {
        return $this->monitoringItemId;
    }
}

<?php

namespace Elements\Bundle\ProcessManagerBundle\Message;

class StopProcessMessage
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

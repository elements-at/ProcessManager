<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Message;

class StopProcessMessage
{
    public function __construct(
        private readonly int $monitoringItemId,
    ) {
    }

    public function getMonitoringItemId(): int
    {
        return $this->monitoringItemId;
    }
}

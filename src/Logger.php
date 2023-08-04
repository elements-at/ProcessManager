<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle;

use Pimcore\Bundle\ApplicationLoggerBundle\ApplicationLogger;

class Logger extends ApplicationLogger
{
    public function log($level, $message, array $context = []): void
    {
        parent::log($level, $message, $context);
        $monitoringItem = \Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle::getMonitoringItem();

        if(($check = $monitoringItem->getCriticalErrorLevel()) && in_array($level, $check)) {
            $monitoringItem->setHasCriticalError(true)->save();
        }
    }

    public function closeLoggerHandlers(): void
    {

        /**
         * @var \Monolog\Logger $logger
         */
        foreach($this->loggers as $logger) {
            foreach($logger->getHandlers() as $handler) {
                $handler->close();
            }
        }
    }
}

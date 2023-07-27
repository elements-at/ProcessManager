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

class Logger extends \Pimcore\Log\ApplicationLogger
{
    public function log($level, $message, array $context = []): void
    {
        parent::log($level, $message, $context);
        $monitoringItem = \Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle::getMonitoringItem();

        if($check = $monitoringItem->getCriticalErrorLevel()) {
            if(in_array($level, $check)) {
                $monitoringItem->setHasCriticalError(true)->save();
            }
        }
    }

    public function closeLoggerHandlers(): void
    {

        /**
         * @var \Monolog\Logger $logger
         */
        foreach($this->loggers as $logger) {
            foreach($logger->getHandlers() ?? [] as $handler) {
                $handler->close();
            }
        }
    }
}

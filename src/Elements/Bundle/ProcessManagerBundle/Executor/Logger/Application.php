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

namespace Elements\Bundle\ProcessManagerBundle\Executor\Logger;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

class Application extends AbstractLogger
{
    protected $streamHandler = null;
    public $name = 'application';
    public $extJsClass = 'pimcore.plugin.processmanager.executor.logger.application';

    /**
     * @param $monitoringItem MonitoringItem
     * @param $loggerData
     *
     * @return string
     */
    public function getGridLoggerHtml($monitoringItem, $loggerData)
    {
        return '<a href="#" onClick="processManagerApplicationLogger.showLogs('.$monitoringItem->getId(
            ).','.(int)$loggerData['index'].');return false;" class=" " alt="Show logs"><img src="/bundles/pimcoreadmin/img/flat-color-icons/rules.svg" alt="Application Logger" height="18" title="Application Logger"/></a>';
    }

    public function createStreamHandler($config, $monitoringItem)
    {
        if (!$this->streamHandler) {
            if (!$config['logLevel']) {
                $config['logLevel'] = 'DEBUG';
            }
            $logLevel = constant('\Psr\Log\LogLevel::'.$config['logLevel']);

            return new \Pimcore\Log\Handler\ApplicationLoggerDb(\Pimcore\Db::get(), $logLevel);
        }

        return $this->streamHandler;
    }
}

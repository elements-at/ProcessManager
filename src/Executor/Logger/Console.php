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
use Monolog\Handler\StreamHandler;

class Console extends AbstractLogger
{
    protected $streamHandler = null;

    public $name = 'console';

    public $extJsClass = 'pimcore.plugin.processmanager.executor.logger.console';

    /**
     * @param $monitoringItem MonitoringItem
     * @param $loggerData
     *
     * @return string
     */
    public function getGridLoggerHtml($monitoringItem, $loggerData)
    {
        return '';
    }

    public function createStreamHandler($config, $monitoringItem)
    {
        if (!$this->streamHandler && php_sapi_name() === 'cli') {
            if (empty($config['logLevel'])) {
                $config['logLevel'] = 'DEBUG';
            }

            $logLevel = constant('\Psr\Log\LogLevel::'.$config['logLevel']);
            $this->streamHandler = new StreamHandler('php://stdout', $logLevel);

            if ($config['simpleLogFormat'] ?? false) {
                $this->streamHandler->setFormatter(new \Monolog\Formatter\LineFormatter(self::LOG_FORMAT_SIMPLE));
            }
        }

        return $this->streamHandler;
    }
}

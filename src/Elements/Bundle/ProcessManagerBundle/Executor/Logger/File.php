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
use Monolog\Logger;

class File extends AbstractLogger
{
    protected $streamHandler = null;
    public $name = 'file';
    public $extJsClass = 'pimcore.plugin.processmanager.executor.logger.file';

    /**
     * @param $monitoringItem MonitoringItem
     * @param $loggerData
     *
     * @return string
     */
    public function getGridLoggerHtml($monitoringItem, $loggerData)
    {
        $logFile = $this->getLogFile($loggerData, $monitoringItem);
        if (is_readable($logFile)) {
            return '<a href="#" onClick="processManagerFileLogger.showLogs('.$monitoringItem->getId(
                ).','.(int)$loggerData['index'].');return false;" class="process_manager_icon_download" alt="Show logs"><img src="/bundles/pimcoreadmin/img/flat-color-icons/file-border.svg" alt="Download" height="18" title="File Logger"/></a>';
        }
    }

    public function createStreamHandler($config, $monitoringItem)
    {
        if (!$this->streamHandler) {
            if (!$config['logLevel']) {
                $config['logLevel'] = 'DEBUG';
            }
            $logLevel = constant('\Psr\Log\LogLevel::'.$config['logLevel']);
            $logFile = $this->getLogFile($config, $monitoringItem);

            if (php_sapi_name() === 'cli' && $config['maxFileSizeMB'] && is_readable($logFile)) {
                $logFileSize = round(filesize($logFile) / 1024 / 1024);

                /**
                 * remove half of the data until the file size is within the range
                 */
                while ($logFileSize > $config['maxFileSizeMB']) {
                    clearstatcache(); //clear php internal cache otherwise the size won't be correct

                    $monitoringItem->getLogger()->notice('Log file size exceeded. Filesize: ' . $logFileSize.'MB. Max file size: ' . $config['maxFileSizeMB'] . '. Removing old data.');
                    $data = explode("\n", file_get_contents($logFile));
                    $data = array_slice($data, count($data) / 2);
                    file_put_contents($logFile, implode("\n", $data));
                    $logFileSize = round(filesize($logFile) / 1024 / 1024);
                    $monitoringItem->getLogger()->notice('New file size: ' . $logFileSize . 'MB.');
                    \Pimcore::collectGarbage();
                }
            }

            $this->streamHandler = new StreamHandler($logFile, $logLevel);

            if ($config['simpleLogFormat']) {
                $this->streamHandler->setFormatter(new \Monolog\Formatter\LineFormatter(self::LOG_FORMAT_SIMPLE));
            }
        }

        return $this->streamHandler;
    }

    /**
     * @param $config
     * @param $monitoringItem MonitoringItem
     *
     * @return string
     */
    public function getLogFile($config, $monitoringItem)
    {
        if ($v = $config['filepath']) {
            $logFile = PIMCORE_PROJECT_ROOT.$v;
        } else {
            $logFile = $monitoringItem->getLogFile();
        }

        return $logFile;
    }
}

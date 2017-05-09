<?php

namespace Elements\Bundle\ProcessManagerBundle\Executor\Logger;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class File extends AbstractLogger
{

    protected $streamHandler = null;
    public $name = 'file';
    public $extJsClass = 'pimcore.plugin.processmanager.executor.logger.file';

    /**
     * @param $monitoringItem MonitoringItem
     *
     * @param $loggerData
     * @return string
     */
    public function getGridLoggerHtml($monitoringItem, $loggerData)
    {
        $logFile = $this->getLogFile($loggerData, $monitoringItem);
        if (is_readable($logFile)) {
            return '<a href="#" onClick="processManagerFileLogger.showLogs('.$monitoringItem->getId(
                ).','.(int)$loggerData['index'].');return false;" class="process_manager_icon_download" alt="Show logs"><img src="/pimcore/static6/img/flat-color-icons/file-border.svg" alt="Download" height="18" title="File Logger"/></a>';
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

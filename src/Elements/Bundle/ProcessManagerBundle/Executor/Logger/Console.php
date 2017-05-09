<?php

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
     *
     * @param $loggerData
     * @return string
     */
    public function getGridLoggerHtml($monitoringItem, $loggerData)
    {
        return '';
    }

    public function createStreamHandler($config, $monitoringItem)
    {
        if (!$this->streamHandler && php_sapi_name() === 'cli') {
            if (!$config['logLevel']) {
                $config['logLevel'] = 'DEBUG';
            }

            $logLevel = constant('\Psr\Log\LogLevel::'.$config['logLevel']);
            $this->streamHandler = new StreamHandler('php://stdout', $logLevel);

            if ($config['simpleLogFormat']) {
                $this->streamHandler->setFormatter(new \Monolog\Formatter\LineFormatter(self::LOG_FORMAT_SIMPLE));
            }
        }

        return $this->streamHandler;
    }

}

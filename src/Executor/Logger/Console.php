<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor\Logger;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Monolog\Handler\StreamHandler;

class Console extends AbstractLogger
{
    protected StreamHandler|null $streamHandler = null;

    public string $name = 'console';

    public string $extJsClass = 'pimcore.plugin.processmanager.executor.logger.console';

    /**
     * @param $monitoringItem MonitoringItem
     * @param array<mixed> $actionData
     *
     * @return string
     */
    public function getGridLoggerHtml(MonitoringItem $monitoringItem, array $actionData): string
    {
        return '';
    }

    /**
     * @param array<mixed> $config
     * @param MonitoringItem $monitoringItem
     */
    public function createStreamHandler(array $config, MonitoringItem $monitoringItem): ?StreamHandler
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

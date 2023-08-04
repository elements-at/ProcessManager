<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor\Logger;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Monolog\Handler\StreamHandler;
use Pimcore\Bundle\ApplicationLoggerBundle\Handler\ApplicationLoggerDb;

class Application extends AbstractLogger
{
    protected $streamHandler = null;

    public string $name = 'application';

    public string $extJsClass = 'pimcore.plugin.processmanager.executor.logger.application';

    /**
     * @param $monitoringItem MonitoringItem
     * @param array<mixed> $actionData
     *
     * @return string
     */
    public function getGridLoggerHtml(MonitoringItem $monitoringItem, array $actionData): string
    {
        return '<a href="#" onClick="processManagerApplicationLogger.showLogs('.$monitoringItem->getId(
        ).','.(int)$actionData['index'].');return false;" class=" " alt="Show logs"><img src="/bundles/pimcoreadmin/img/flat-color-icons/rules.svg" alt="Application Logger" height="18" title="Application Logger"/></a>';
    }

    /**
     * @param array<mixed> $config
     *
     * @return StreamHandler|null
     */
    public function createStreamHandler(array $config, MonitoringItem $monitoringItem): ?StreamHandler
    {
        if (!$this->streamHandler) {
            if (!$config['logLevel']) {
                $config['logLevel'] = 'DEBUG';
            }
            $logLevel = constant('\Psr\Log\LogLevel::'.$config['logLevel']);

            return new ApplicationLoggerDb(\Pimcore\Db::get(), $logLevel);
        }

        return $this->streamHandler;
    }
}

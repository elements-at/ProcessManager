<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor\Logger;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Bundle\ApplicationLoggerBundle\Handler\ApplicationLoggerDb;

class Application extends AbstractLogger
{
    protected ApplicationLoggerDb $streamHandler;

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

        return '<a href="#"
                        data-process-manager-trigger="showApplicationLogs"
                data-process-manager-id="' . $monitoringItem->getId() . '"
                data-process-manager-action-index="' . (int)$actionData['index'] . '"
        class=" " alt="Show logs"><img src="/bundles/pimcoreadmin/img/flat-color-icons/text.svg" alt="Application Logger" height="18" title="Application Logger"/></a>';
    }

    /**
     * @param array<mixed> $config
     *
     */
    public function createStreamHandler(array $config, MonitoringItem $monitoringItem): ApplicationLoggerDb
    {
        if (!isset($this->streamHandler)) {
            if (!$config['logLevel']) {
                $config['logLevel'] = 'DEBUG';
            }
            $logLevel = constant('\Psr\Log\LogLevel::'.$config['logLevel']);

            $this->streamHandler = new ApplicationLoggerDb(\Pimcore\Db::get(), $logLevel);
        }

        return $this->streamHandler;
    }
}

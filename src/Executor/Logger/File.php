<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor\Logger;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Monolog\Handler\StreamHandler;

class File extends AbstractLogger
{
    protected StreamHandler|null $streamHandler = null;

    public string $name = 'file';

    public string $extJsClass = 'pimcore.plugin.processmanager.executor.logger.file';

    /**
     * @param $monitoringItem MonitoringItem
     * @param array<mixed> $actionData
     *
     * @return string
     */
    public function getGridLoggerHtml(MonitoringItem $monitoringItem, array $actionData): string
    {
        $logFile = $this->getLogFile($actionData, $monitoringItem);
        if (is_readable($logFile)) {
            $icon = ($actionData['icon'] ?? null) ?: '/bundles/pimcoreadmin/img/flat-color-icons/file-border.svg';
            $title = ($actionData['title'] ?? null) ?: 'File Logger';

            return '<a href="#"
                data-process-manager-trigger="showLogs"
                data-process-manager-id="' . $monitoringItem->getId() . '"
                data-process-manager-action-index="' . (int)$actionData['index'] . '"
                ><img src="' . $icon . '" alt="Download" height="18" title="' . $title . '"/></a>';
        }

        return '';
    }

    /**
     * @param array<mixed> $config
     * @param MonitoringItem $monitoringItem
     *
     * @return StreamHandler|null
     */
    public function createStreamHandler(array $config, MonitoringItem $monitoringItem): ?StreamHandler
    {
        if (!$this->streamHandler) {
            if (empty($config['logLevel'])) {
                $config['logLevel'] = 'DEBUG';
            }
            $logLevel = constant('\Psr\Log\LogLevel::' . $config['logLevel']);
            $logFile = $this->getLogFile($config, $monitoringItem);

            if (!array_key_exists('maxFileSizeMB', $config)) {
                $config['maxFileSizeMB'] = null;
            }

            if (php_sapi_name() === 'cli' && $config['maxFileSizeMB'] && is_readable($logFile)) {
                $logFileSize = round(filesize($logFile) / 1024 / 1024);

                /**
                 * remove half of the data until the file size is within the range
                 */
                while ($logFileSize > $config['maxFileSizeMB']) {
                    clearstatcache(); //clear php internal cache otherwise the size won't be correct

                    $monitoringItem->getLogger()->notice('Log file size exceeded. Filesize: ' . $logFileSize . 'MB. Max file size: ' . $config['maxFileSizeMB'] . '. Removing old data.');
                    $data = explode("\n", file_get_contents($logFile));
                    $data = array_slice($data, count($data) / 2);
                    file_put_contents($logFile, implode("\n", $data));
                    $logFileSize = round(filesize($logFile) / 1024 / 1024);
                    $monitoringItem->getLogger()->notice('New file size: ' . $logFileSize . 'MB.');
                    \Pimcore::collectGarbage();
                }
            }

            $this->streamHandler = new StreamHandler($logFile, $logLevel);

            if ($config['simpleLogFormat'] ?? false) {
                $this->streamHandler->setFormatter(new \Monolog\Formatter\LineFormatter(self::LOG_FORMAT_SIMPLE));
            }
        }

        return $this->streamHandler;
    }

    /**
     * @param array<mixed> $config
     * @param $monitoringItem MonitoringItem
     *
     * @return string
     */
    public function getLogFile(array $config, MonitoringItem $monitoringItem)
    {
        return ($v = $config['filepath'] ?? null) ? PIMCORE_PROJECT_ROOT . $v : $monitoringItem->getLogFile();
    }
}

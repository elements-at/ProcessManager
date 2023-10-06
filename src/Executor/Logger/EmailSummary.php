<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor\Logger;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Filesystem\Filesystem;

class EmailSummary extends AbstractLogger
{
    protected StreamHandler|null $streamHandler = null;

    public string $name = 'emailSummary';

    public string $extJsClass = 'pimcore.plugin.processmanager.executor.logger.emailSummary';

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
     *
     * @return StreamHandler|null
     *
     * @throws \JsonException
     */
    public function createStreamHandler(array $config, MonitoringItem $monitoringItem): ?StreamHandler
    {
        if (!$this->streamHandler) {
            if (empty($config['logLevel'])) {
                $config['logLevel'] = 'DEBUG';
            }

            $logLevel = constant('\Psr\Log\LogLevel::'.$config['logLevel']);

            $logFile = $this->getLogFile($monitoringItem, $config);
            $this->streamHandler = new StreamHandler($logFile, $logLevel);

            if ($config['simpleLogFormat'] ?? false) {
                $this->streamHandler->setFormatter(new \Monolog\Formatter\LineFormatter(self::LOG_FORMAT_SIMPLE));
            }
        }

        return $this->streamHandler;
    }

    /**
     * @param $monitoringItem MonitoringItem
     * @param array<mixed> $config
     *
     * @return string
     *
     * @throws \JsonException
     */
    public function getLogFile(MonitoringItem $monitoringItem, array $config): string
    {
        $dir = \Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle::getLogDir().'email/' . $monitoringItem->getId() ;

        if(!is_dir($dir)) {
            $filesystem = new Filesystem();
            $filesystem->mkdir($dir, 0755);
        }

        return $dir . ('/'.md5(json_encode($config, JSON_THROW_ON_ERROR)).'.log');
    }

    /**
     * @param array<mixed> $loggerConfig
     *
     * @return array<mixed>
     */
    public function handleShutdown(MonitoringItem $monitoringItem, array $loggerConfig): array
    {

        $logFile = $this->getLogFile($monitoringItem, $loggerConfig);
        if($file = is_file($logFile)) {
            $mail = new \Pimcore\Mail();
            $mail->subject($loggerConfig['subject']);

            $to = array_filter(explode(';', (string) $loggerConfig['to']));
            if($to !== []) {
                foreach($to as &$email) {
                    $email = trim($email);
                    $mail->addTo($email);
                }

                $mail->text($loggerConfig['text']."\n\n\nProcess ID: ".$monitoringItem->getId()."\nERROR LOG:\n" . file_get_contents($logFile));
                $mail->send();
            }
            unlink($logFile);
        }

        return ['reportedDate' => date('Y-m-d H:i:s')];
    }
}

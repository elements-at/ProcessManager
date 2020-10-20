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
use Monolog\Handler\SwiftMailerHandler;

class EmailSummary extends AbstractLogger
{
    protected $streamHandler = null;
    public $name = 'emailSummary';
    public $extJsClass = 'pimcore.plugin.processmanager.executor.logger.emailSummary';

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
        if (!$this->streamHandler) {
            if (empty($config['logLevel'])) {
                $config['logLevel'] = 'DEBUG';
            }

            $logLevel = constant('\Psr\Log\LogLevel::'.$config['logLevel']);


            $logFile = $this->getLogFile($monitoringItem,$config);
            $this->streamHandler = new StreamHandler($logFile,$logLevel);

            if ($config['simpleLogFormat'] ?? false) {
                $this->streamHandler->setFormatter(new \Monolog\Formatter\LineFormatter(self::LOG_FORMAT_SIMPLE));
            }
        }

        return $this->streamHandler;
    }

    public function getLogFile($monitoringItem,$config){
        $dir = \Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle::getLogDir().'email/' . $monitoringItem->getId() ;
        if(!is_dir($dir)){
            \Pimcore\File::mkdir($dir);
        }
        $dir .= '/'.md5(json_encode($config)).'.log' ;
        return $dir;
    }

    /**
     * @param $monitoringItem
     * @param array $loggerConfig
     */
    public function handleShutdown(MonitoringItem $monitoringItem, array $loggerConfig){

        $logFile = $this->getLogFile($monitoringItem,$loggerConfig);
        if($file = is_file($logFile)){
            $mail = new \Pimcore\Mail();
            $mail->setSubject($loggerConfig['subject']);

            $to = array_filter(explode(';',$loggerConfig['to']));
            if($to){
                foreach($to as &$email){
                    $email = trim($email);
                }

                $mail->addTo($to);
                $mail->setBodyText($loggerConfig['text']."\n\n\nProcess ID: ".$monitoringItem->getId()."\nERROR LOG:\n" . file_get_contents($logFile));
                $mail->send();
            }
            unlink($logFile);
        }
        return ['reportedDate' => date('Y-m-d H:i:s')];
    }

}

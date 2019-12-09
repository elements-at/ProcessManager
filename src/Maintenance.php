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

namespace Elements\Bundle\ProcessManagerBundle;

use Carbon\Carbon;
use Elements\Bundle\ProcessManagerBundle\Model\CallbackSetting;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Symfony\Component\Templating\EngineInterface;

class Maintenance
{
    /**
     * @var MonitoringItem
     */
    protected $monitoringItem;

    protected $renderingEngine;

    public function __construct(EngineInterface $renderingEngine)
    {
        $this->renderingEngine = $renderingEngine;
    }

    public function execute()
    {
        $this->monitoringItem = ElementsProcessManagerBundle::getMonitoringItem();
        $this->monitoringItem->setTotalSteps(3)->save();
        $this->checkProcesses();
        $this->executeCronJobs();
        $this->clearMonitoringLogs();
    }

    public function checkProcesses()
    {
        $this->monitoringItem->setCurrentStep(1)->setStatus('Checking processes')->save();

        $this->monitoringItem->getLogger()->debug('Checking processes');

        $list = new MonitoringItem\Listing();
        $list->setCondition('IFNULL(reportedDate,0) = 0 ');
        $items = $list->load();
        $reportItems = $itemsAlive = [];

        $config = ElementsProcessManagerBundle::getConfig();

        foreach ($items as $item) {
            if (!$item->getCommand()) { //manually created - do not check
                $item->setReportedDate(1)->save(true);
            } else {
                if ($item->isAlive()) {
                    $diff = time() - $item->getModificationDate();
                    $minutes = $config['general']['processTimeoutMinutes'] ?: 15;
                    if ($diff > (60 * $minutes)) {
                        $item->getLogger()->error('Process was checked by ProcessManager maintenance. Considered as hanging process - TimeDiff: ' . $diff . ' seconds.');
                        $reportItems[] = $item;
                        $itemsAlive[] = true;
                    }
                } else {
                    Helper::executeMonitoringItemLoggerShutdown($item, true);
                    if ($item->getStatus() == $item::STATUS_FINISHED) {
                        $item->getLogger()->info('Process was checked by ProcessManager maintenance and considered as successfull process.');
                        $item->setReportedDate(time())->save(true);
                    } else {
                        $item->setMessage('Process died. ' . $item->getMessage() . ' Last State: ' . $item->getStatus(), false)->setStatus($item::STATUS_FAILED);
                        $item->getLogger()->error('Process was checked by ProcessManager maintenance and considered as dead process');
                        $this->monitoringItem->getLogger()->error('Monitoring item ' . $item->getId() . ' was checked by ProcessManager maintenance and considered as dead process');
                        $reportItems[] = $item;
                        $itemsAlive[] = false;
                    }
                }
            }
        }

        if ($reportItems) {
            $config = ElementsProcessManagerBundle::getConfig();
            $mail = new \Pimcore\Mail();
            $mail->setSubject('ProcessManager - failed processes (' . \Pimcore\Tool::getHostUrl().')');

            $html = $this->renderingEngine->render('ElementsProcessManagerBundle::report-email.html.php', [
                'reportItems' => $reportItems
            ]);

            $mail->setBodyHtml($html);

            $recipients = $config['email']['recipients'];
            if (is_string($recipients) && !empty($recipients)) {
                $recipients = array_filter(explode(';', $config['email']['recipients']));
            }

            if ($recipients) {

                foreach($recipients as $emailAdr){
                    $mail->addTo($emailAdr);
                }
                
                try {
                    $mail->send();
                } catch (\Exception $e) {
                    $logger = \Pimcore\Log\ApplicationLogger::getInstance('ProcessManager', true); // returns a PSR-3 compatible logger
                    $message = "Can't send E-Mail: " . $e->getMessage();
                    $logger->emergency($message);
                    \Pimcore\Logger::emergency($message);
                }
            }
        }
        /**
         * @var $item MonitoringItem
         */
        foreach ($reportItems as $key => $item) {
            $item->setReportedDate(time())->save($itemsAlive[$key]);
        }
        $this->monitoringItem->setStatus('Processes checked')->save();
    }

    public function executeCronJobs()
    {
        $this->monitoringItem->setCurrentStep(2)->setMessage('Checking cronjobs')->save();

        $logger = $this->monitoringItem->getLogger();
        $list = new Configuration\Listing();
        $list->setCondition('cronjob != "" AND active=1 ');
        $configs = $list->load();
        $logger->notice('Checking ' . count($configs).' Jobs');
        foreach ($configs as $config) {
            $currentTs = time();
            $nextRunTs = $config->getNextCronJobExecutionTimestamp();

            $message = 'Checking Job: ' . $config->getName().' (ID: '.$config->getId().') Last execution: ' . date('Y-m-d H:i:s', $config->getLastCronJobExecution());
            $message .= ' Next execution: ' . date('Y-m-d H:i:s', $nextRunTs);
            $logger->debug($message);
            $diff = $nextRunTs - $currentTs;
            if ($diff <= 0) {
                $params = [];
                //add default callback settings if defined
                if ($settings = $config->getExecutorSettings()) {
                    $settings = json_decode($settings, true);
                    $preDefinedConfigId = $settings['values']['defaultPreDefinedConfig'];
                    if ($preDefinedConfigId) {
                        $callbackSetting = CallbackSetting::getById($preDefinedConfigId);
                        if ($callbackSetting) {
                            if ($v = $callbackSetting->getSettings()) {
                                $params = json_decode($v, true);
                            }
                        }
                    }
                }

                $result = Helper::executeJob($config->getId(), $params, 0);
                if ($result['success']) {
                    $logger->debug('Execution job: ' . $config->getName().' ID: ' . $config->getId().' Diff:' . $diff.' Command: '. $result['executedCommand']);
                    $config->setLastCronJobExecution(time())->save();
                } else {
                    $logger->info("Can't start the Cronjob. Data: " . print_r($result, true));
                }
            } else {
                $logger->debug('Skipping job: ' . $config->getName().' ID: ' . $config->getId().' Diff:' . $diff);
            }
        }

        $this->monitoringItem->setMessage('Cronjobs executed')->setCompleted();
    }

    protected function clearMonitoringLogs()
    {
        $this->monitoringItem->setCurrentStep(3)->setMessage('Clearing monitoring logs')->save();
        $logger = $this->monitoringItem->getLogger();

        $treshold = ElementsProcessManagerBundle::getConfig()['general']['archive_treshold_logs'];
        if ($treshold) {
            $timestamp = Carbon::createFromTimestamp(time())->subDay(1)->getTimestamp();
            $list = new MonitoringItem\Listing();
            $list->setCondition('modificationDate <= '. $timestamp);
            $items = $list->load();
            $logger->debug('Deleting ' . count($items).' monitoring items.');
            foreach ($items as $item) {
                $logger->debug('Deleting item. Name: "' . $item->getName().'" ID: '.$item->getId() .' monitoring items.');
                $item->delete();
            }
        } else {
            $logger->notice('No treshold defined -> nothing to do.');
        }

        $logger->debug('Start clearing ProcessManager maintenance items');
        $list = new MonitoringItem\Listing();
        $list->setCondition('name ="ProcessManager maintenance" AND status="finished"');
        $list->setOrderKey('id')->setOrder('DESC');
        $list->setOffset(5);
        foreach ($list->load() as $item) {
            $logger->debug('Deleting monitoring Item: ' . $item->getId());
            $item->delete();
        }
        $logger->debug('Clearing ProcessManager items finished');

        $this->monitoringItem->setMessage('Clearing monitoring done')->setCompleted();
    }
}

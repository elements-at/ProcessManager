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
        $this->deleteExpiredMonitoringItems();
    }

    protected function deleteExpiredMonitoringItems()
    {
        $list = new Model\MonitoringItem\Listing();
        $list->setCondition('(status = "'.MonitoringItem::STATUS_FINISHED.'" OR status = "'.MonitoringItem::STATUS_FINISHED_WITH_ERROR.'") AND (deleteAfterHours > 0 AND (UNIX_TIMESTAMP()-(deleteAfterHours*3600)) > modificationDate)');

        $items = $list->load();

        foreach($items as $item) {
            $ts = time()-$item->getModificationDate();
            $modDate = \Carbon\Carbon::createFromTimestamp($item->getModificationDate());
            $diff = $modDate->diffInHours(\Carbon\Carbon::now());
            $item->getLogger()->debug('Delete item ' . $item->getId() .' Name: '. $item->getName(). ' because it expired. Hours diff: ' . $diff);
            $item->delete();
        }
    }

    public function checkProcesses()
    {
        $this->monitoringItem->setCurrentStep(1)->setStatus('Checking processes')->save();

        $this->monitoringItem->getLogger()->debug('Checking processes');

        $list = new MonitoringItem\Listing();
        $list->setCondition('IFNULL(reportedDate,0) = 0 ');
        $items = $list->load();
        $reportItems = $itemsAlive = [];

        $config = ElementsProcessManagerBundle::getConfiguration();

        foreach ($items as $item) {
            if (!$item->getCommand()) { //manually created - do not check
                $item->setReportedDate(1)->save();
            } else {
                if ($item->isAlive()) {
                    $diff = time() - $item->getModificationDate();
                    $minutes = $config->getProcessTimeoutMinutes();
                    if ($diff > (60 * $minutes)) {
                        $item->getLogger()->error('Process was checked by ProcessManager maintenance. Considered as hanging process - TimeDiff: ' . $diff . ' seconds.');
                        $reportItems[] = $item;
                        $itemsAlive[] = true;
                    }
                } else {
                    Helper::executeMonitoringItemLoggerShutdown($item, true);
                    if ($item->getStatus() == $item::STATUS_FINISHED) {
                        $item->getLogger()->info('Process was checked by ProcessManager maintenance and considered as successfull process.');
                        $item->setReportedDate(time())->save();
                    } else {
                        $item->setMessage('Process died. ' . $item->getMessage() . ' Last State: ' . $item->getStatus(), false)->setStatus($item::STATUS_FAILED);
                        $item->getLogger()->error('Process was checked by ProcessManager maintenance and considered as dead process');
                        $this->monitoringItem->getLogger()->error('Monitoring item ' . $item->getId() . ' was checked by ProcessManager maintenance and considered as dead process');
                        $reportItems[] = $item;
                        $itemsAlive[] = false;
                    }
                }
                $item->getLogger()->closeLoggerHandlers();
            }
        }

        if ($reportItems) {
            $config = ElementsProcessManagerBundle::getConfiguration();
            $recipients = $config->getReportingEmailAddresses();
            if ($recipients) {
                $mail = new \Pimcore\Mail();
                $mail->setSubject('ProcessManager - failed processes (' . \Pimcore\Tool::getHostUrl().')');

                $html = $this->renderingEngine->render('@ElementsProcessManager/reportEmail.html.twig', [
                    'totalItemsCount' => count($reportItems),
                    'reportItems' => array_slice($reportItems, 0, 5)
                ]);

                $mail->html($html);

                foreach($recipients as $emailAdr) {
                    try {
                        $mail->addTo($emailAdr);
                    } catch (\Exception $e) {
                        $logger = \Pimcore\Log\ApplicationLogger::getInstance('ProcessManager', true); // returns a PSR-3 compatible logger
                        $message = "Can't add E-Mail address : " . $e->getMessage();
                        $logger->emergency($message);
                        \Pimcore\Logger::emergency($message);
                    }
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
            $item->setReportedDate(time())->save();
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
                    $settings = json_decode((string) $settings, true, 512, JSON_THROW_ON_ERROR);
                    if (isset($settings['values']['defaultPreDefinedConfig'])) {
                        $preDefinedConfigId = $settings['values']['defaultPreDefinedConfig'];
                        $callbackSetting = CallbackSetting::getById($preDefinedConfigId);
                        if ($callbackSetting) {
                            if ($v = $callbackSetting->getSettings()) {
                                $params = json_decode($v, true, 512, JSON_THROW_ON_ERROR);
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

        $threshold = ElementsProcessManagerBundle::getConfiguration()->getArchiveThresholdLogs();
        if ($threshold) {
            $timestamp = Carbon::createFromTimestamp(time())->subDay()->getTimestamp();
            $list = new MonitoringItem\Listing();
            $list->setCondition('modificationDate <= '. $timestamp);
            $items = $list->load();
            $logger->debug('Deleting ' . count($items).' monitoring items.');
            foreach ($items as $item) {
                $logger->debug('Deleting item. Name: "' . $item->getName().'" ID: '.$item->getId() .' monitoring items.');
                $item->delete();
            }
        } else {
            $logger->notice('No threshold defined -> nothing to do.');
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

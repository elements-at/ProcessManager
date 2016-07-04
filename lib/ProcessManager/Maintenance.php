<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 28.06.2016
 * Time: 11:47
 */

namespace ProcessManager;


use Pimcore\Model\Schedule\Task\Executor;
use Carbon\Carbon;

class Maintenance {

    /**
     * @var MonitoringItem
     */
    protected $monitoringItem;

    public function execute(){
        $this->monitoringItem = Plugin::getMonitoringItem();
        $this->monitoringItem->setTotalSteps(3)->save();
        $this->checkProcesses();
        $this->executeCronJobs();
        $this->clearMonitoringLogs();
    }

    public function checkProcesses(){
        $this->monitoringItem->setCurrentStep(1)->setStatus('Checking processes')->save();

        $this->monitoringItem->getLogger()->debug('Checking processes');

        $list = new MonitoringItem\Listing();
        $list->setCondition('pid <> "" AND IFNULL(reportedDate,0) = 0 ');
        $items = $list->load();
        $reportItems = [];
        foreach($items as $item){
            if(!$item->isAlive()){
                $item->setMessage('Process died. ' . $item->getMessage().' Last State: ' . $item->getStatus())->setStatus($item::STATUS_FAILED);
                $item->getLogger()->error('Process was checked by ProcessManager maintenance and considered as dead process. PID ' . $item->getPid().' not available.');
                $reportItems[] = $item;
            }
        }

        if($reportItems){
            $config = Plugin::getPluginConfig();
            $mail = new \Pimcore\Mail();
            $mail->setSubject('ProcessManager - failed processes (' . \Pimcore\Tool::getHostUrl().')');
            $view = new \Pimcore\View();
            $view->reportItems = $reportItems;
            $html = $view->setBasePath(PIMCORE_DOCUMENT_ROOT.'/plugins/ProcessManager/views')->render('report-email.php');
            $mail->setBodyHtml($html);

            $recipients = $config['email']['recipients'];
            $mail->addTo(array_shift($recipients));
            if(!empty($recipients)){
                $mail->addCc($recipients);
            }
            $mail->send();
        }
        /**
         * @var $item MonitoringItem
         */
        foreach($reportItems as $item){
            $item->setReportedDate(time())->save();
        }
        $this->monitoringItem->setStatus('Processes checked')->save();
    }

    public function executeCronJobs(){
        $this->monitoringItem->setCurrentStep(2)->setMessage('Checking cronjobs')->save();

        $logger = $this->monitoringItem->getLogger();
        $list = new Configuration\Listing();
        $list->setCondition('cronjob != "" AND active=1 ');
        $configs = $list->load();
        $logger->notice('Checking ' . count($configs).' Jobs');
        foreach($configs as $config){
            $currentTs = time();
            $nextRunTs = $config->getNextCronJobExecutionTimestamp();

            $message = 'Checking Job: ' . $config->getName().' (ID: '.$config->getId().') Last execution: ' . date('Y-m-d H:i:s',$config->getLastCronJobExecution());
            $message .= ' Next execution: ' . date('Y-m-d H:i:s',$nextRunTs);
            $logger->debug($message);
            $diff = $nextRunTs-$currentTs;
            if($diff < 0){
            #if(true){
                $logger->debug('Execution job: ' . $config->getName().' ID: ' . $config->getId().' Diff:' . $diff);
                $command = $config->getExecutorClassObject()->getCommand();
                $logger->notice('Executing Command: "' . $command.'" ');
                \Pimcore\Tool\Console::execInBackground($command);
                $config->setLastCronJobExecution(time())->save();
            }else{
                $logger->debug('Skipping job: ' . $config->getName().' ID: ' . $config->getId().' Diff:' . $diff);
            }
        }

        $this->monitoringItem->setMessage('Cronjobs executed')->setCompleted();
    }

    protected function clearMonitoringLogs(){
        $this->monitoringItem->setCurrentStep(3)->setMessage('Clearing monitoring logs')->save();
        $logger = $this->monitoringItem->getLogger();

        $treshold =  Plugin::getPluginConfig()['general']['archive_treshold_logs'];
        if($treshold){
            $timestamp = Carbon::createFromTimestamp(time())->subDay(1)->getTimestamp();
            $list = new MonitoringItem\Listing();
            $list->setCondition('modificationDate <= '. $timestamp);
            $items = $list->load();
            $logger->debug('Deleting ' . count($items).' monitoring items.');
            foreach($items as $item){
                $logger->debug('Deleting item. Name: "' . $item->getName().'" ID: '.$item->getId() .' monitoring items.');
                $item->delete();
            }
        }else{
            $logger->notice('No treshold defined -> nothing to do.');
        }

        $logger->debug("Start clearing ProcessManager maintenance items");
        $list = new MonitoringItem\Listing();
        $list->setCondition('name ="ProcessManager maintenance" AND status="finished"');
        $list->setOrderKey('id')->setOrder('DESC');
        $list->setOffset(5);
        foreach($list->load() as $item){
            $logger->debug("Deleting monitoring Item: " . $item->getId());
            $item->delete();
        }
        $logger->debug("Clearing ProcessManager items finished");

        $this->monitoringItem->setMessage('Clearing monitoring done')->setCompleted();
    }
}
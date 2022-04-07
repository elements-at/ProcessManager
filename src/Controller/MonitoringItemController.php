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

namespace Elements\Bundle\ProcessManagerBundle\Controller;

use Elements\Bundle\ProcessManagerBundle\Executor\Action\AbstractAction;
use Elements\Bundle\ProcessManagerBundle\Executor\Logger\AbstractLogger;
use Elements\Bundle\ProcessManagerBundle\Executor\Logger\Application;
use Elements\Bundle\ProcessManagerBundle\Executor\Logger\File;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Bundle\AdminBundle\Helper\QueryParams;
use Pimcore\Templating\Model\ViewModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Annotation\Route;
use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Enums;
/**
 * @Route("/admin/elementsprocessmanager/monitoring-item")
 */
class MonitoringItemController extends AdminController
{
    /**
     * @Route("/list")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $this->checkPermission(Enums\Permissions::VIEW);
        $data = [];
        $list = new MonitoringItem\Listing();
        $list->setOrder('DESC');
        $list->setOrderKey('id');
        $list->setLimit($request->get('limit', 25));
        $list->setUser($this->getAdminUser());

        $list->setOffset($request->get('start'));

        $allParams = array_merge($request->request->all(), $request->query->all());
        $sortingSettings = QueryParams::extractSortingSettings($allParams);
        if ($sortingSettings['orderKey'] && $sortingSettings['order']) {
            $list->setOrderKey($sortingSettings['orderKey']);
            $list->setOrder($sortingSettings['order']);
        }

        $callbacks = [
            'executedByUser' => function ($f) {
                $db = \Pimcore\Db::get();
                $ids = $db->fetchCol('SELECT id FROM users where name LIKE '.$db->quote('%'.$f->value.'%')) ?: [0];

                return ' executedByUser IN( '.implode(',', $ids).') ';
            }
        ];
        if ($filterCondition = QueryParams::getFilterCondition(
            $request->get('filter'),
            ['id', 'o_id', 'pid'],
            true,
            $callbacks
        )
        ) {
            $list->setCondition($filterCondition);
        }

        $condition = $list->getCondition();
        if($filters = $request->get('filter')){
            foreach(json_decode($filters,true) as $e){
                if($e['property'] == 'id'){
                    $condition .= ' OR `parentId` = ' . (int)$e['value'].' ';
                }
            }
        }

        if (!$request->get('showHidden') || $request->get('showHidden') == 'false') {
            $filterConditionArray =  QueryParams::getFilterCondition($request->get('filter'), ['id', 'o_id', 'pid'], false, $callbacks);

            if ($filterConditionArray && isset($filterConditionArray['id'])) {
            } else {
                if ($condition) {
                    $condition .= ' AND published=1';
                } else {
                    $condition .= ' published=1';
                }
            }
        }
        $list->setCondition($condition);

        $total = $list->getTotalCount();

        foreach ($list->load() as $item) {
            $data[] = $this->getItemData($item);
        }

        return $this->adminJson(['success' => true, 'total' => $total, 'data' => $data]);
    }

    /**
     * @Route("/update")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update(Request $request){

        $monitoringItem = MonitoringItem::getById($request->get('id'));

        $data = [];

        if($monitoringItem){
            if($monitoringItem->getExecutedByUser() == $this->getUser()->getId()){
                foreach($request->request->all() as $key => $value){
                    $setter = "set" . ucfirst($key);
                    if(method_exists($monitoringItem,$setter)){
                        $monitoringItem->$setter($value);
                    }
                }
                $monitoringItem->save();
            }
            $data = $this->getItemData($monitoringItem);
        }

        return $this->json(['success' => true,'data' => $data]);

    }

    /**
     * @return MonitoringItem\Listing
     */
    protected function getProcessesForCurrentUser(){
        $list = new MonitoringItem\Listing();
        $list->setOrder('DESC');
        $list->setOrderKey('id');
        #$list->setLimit(10);

        $list->setCondition('executedByUser = ? and parentId IS NULL AND published = 1 ',[$this->getUser()->getId()]);
        return $list;
    }



    /**
     * @Route("/update-all-user-monitoring-items")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAllUserMonitoringItems(Request $request){

        $list = $this->getProcessesForCurrentUser();
        $params = $request->request->all();
        /**
         * @var MonitoringItem $item
         */
        foreach($list->load() as $item){
            $item->setValues($params)->save();
        }

        return $this->adminJson(['success' => true]);
    }

    /**
     * @Route("/list-processes-for-user")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listProcessesForUser(Request $request){
        $data = [
            'total' => 0,
            'active' => 0,
            'items' => []
        ];

        try {
            $this->checkPermission(Enums\Permissions::VIEW);
        }catch (\Exception $e){
            return $this->adminJson($data);
        }

        $list = $this->getProcessesForCurrentUser();

        $data['total'] = $list->getTotalCount();

        foreach($list->load() as $item){
            $tmp = $this->getItemData($item);
            if($tmp['isAlive']){
                $data['active']++;
            }
            $data['items'][] = $tmp;
        }

        return $this->adminJson($data);
    }

    protected function getItemData(MonitoringItem $item){
        $tmp = $item->getObjectVars();
        $tmp['messageShort'] = \Pimcore\Tool\Text::cutStringRespectingWhitespace($tmp['message'],30);
        $tmp['steps'] = '-';
        if ($item->getTotalSteps() > 0 || $item->getCurrentStep()) {
            $tmp['steps'] = $item->getCurrentStep().'/'.$item->getTotalSteps();
        }
        $tmp['duration'] = $item->getDuration() ?: '-';
        $tmp['progress'] = 0;

        if ($tmp['executedByUser']) {
            $user = \Pimcore\Model\User::getById($tmp['executedByUser']);
            if ($user) {
                $tmp['executedByUser'] = $user->getName();
            } else {
                $tmp['executedByUser'] = 'User id: '.$tmp['executedByUser'];
            }
        } else {
            $tmp['executedByUser'] = 'System';
        }

        $logFile = 0;
        $tmp['action'] = '';

        if ($actions = $item->getActions()) {
            foreach ($actions as $action) {
                /**
                 * @var $class AbstractAction
                 */
                $class = new $action['class'];
                if ($s = $class->getGridActionHtml($item, $action)) {
                    $tmp['action'] .= $s;
                }
            }
        }
        $tmp['actionItems'] = [];

        if($tmp['actions']){
            $actionItems = json_decode($tmp['actions'],true);

            foreach($actionItems as $i => $v){
                if($class = $v['class']){
                    if(\Pimcore\Tool::classExists($class)){
                        $o = new $class();
                        $v['dynamicData'] = $o->toJson($item,$v);
                    }

                    $actionItems[$i] = $v;
                }
            }
            $tmp['actionItems'] = $actionItems;
        }
        $tmp['logger'] = '';
        if ($loggers = $item->getLoggers()) {
            foreach ((array)$loggers as $i => $logger) {
                /**
                 * @var $class AbstractLogger
                 */
                $class = new $logger['class'];
                if (\Pimcore\Tool::classExists(get_class($class))) {
                    $logger['index'] = $i;
                    if ($s = $class->getGridLoggerHtml($item, $logger)) {
                        $tmp['logger'] .= $s;
                    }
                }
            }
        }

        $tmp['retry'] = 1;
        if ($item->isAlive()) {
            $tmp['retry'] = 0;
        }

        if ($tmp['retry'] == 1) {
            $config = Configuration::getById($item->getConfigurationId());
            if ($config) {
                if ($config->getActive() == 0) {
                    $tmp['retry'] = 0;
                } else {
                        $uniqueExecution = $config->getExecutorClassObject()->getValues()['uniqueExecution'] ?? false;
                        if ($uniqueExecution) {
                        $runningProcesses = $config->getRunningProcesses();
                        if (!empty($runningProcesses)) {
                            $tmp['retry'] = 0;

                        }
                    }
                }
            }
        }
        $tmp['isAlive'] = $item->isAlive();

        $tmp['progress'] = '-';
        if ($item->getCurrentWorkload() && $item->getTotalWorkload()) {
            $progress = $item->getProgressPercentage();
            $tmp['progress'] = '<div class="x-progress x-progress-default x-border-box" style="width:100%;"><div class="x-progress-text x-progress-text-back" ></div><div class="x-progress-bar x-progress-bar-default" style="width:'.$progress.'%;min-width: 35px; "><div class="x-progress-text" style="text-align:left;margin-left: 5px;"><div>'.$progress.'%</div></div></div></div>';
        }

        $tmp['progressPercentage'] = (float)$item->getProgressPercentage();
        $tmp['callbackSettingsString'] = json_encode($item->getCallbackSettings());
        $tmp['callbackSettings'] = $item->getCallbackSettingsForGrid();
        return $tmp;
    }

    /**
     * @Route("/log-application-logger")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function logApplicationLoggerAction(Request $request)
    {
        try {
            $monitoringItem = MonitoringItem::getById($request->get('id'));

            if (!$monitoringItem) {
                throw new \Exception('Monitoring Item with id'.$request->get('id').' not found');
            }
            $loggerIndex = $request->get('loggerIndex');
            if ($loggers = $monitoringItem->getLoggers()) {
                foreach ((array)$loggers as $i => $config) {
                    /**
                     * @var $class AbstractLogger
                     * @var $logger Application
                     */
                    $class = new $config['class'];
                    if (\Pimcore\Tool::classExists(get_class($class))) {
                        if ($i == $loggerIndex) {
                            $logger = $class;
                            if (!$config['logLevel']) {
                                $config['logLevel'] = 'DEBUG';
                            }
                            break;
                        }
                    }
                }
            }

            $result = $monitoringItem->getObjectVars();
            $result['logLevel'] = strtolower($config['logLevel']);

            return $this->adminJson(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return $this->adminJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/log-file-logger")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logFileLoggerAction(Request $request, ?Profiler $profiler)
    {
        if(null !== $profiler) {
            $profiler->disable();
        }
        $viewData = [];
        $monitoringItem = MonitoringItem::getById($request->get('id'));

        $loggerIndex = $request->get('loggerIndex');
        if ($loggers = $monitoringItem->getLoggers()) {
            foreach ((array)$loggers as $i => $config) {
                /**
                 * @var $class AbstractLogger
                 * @var $logger File
                 */
                $class = new $config['class'];
                if (\Pimcore\Tool::classExists(get_class($class))) {
                    if ($i == $loggerIndex) {
                        $logger = $class;
                        $logFile = $logger->getLogFile($config, $monitoringItem);
                        if (!$config['logLevel']) {
                            $config['logLevel'] = 'DEBUG';
                        }
                        break;
                    }
                }
            }
        }
        $viewData['logLevel'] = $config['logLevel'];
        $viewData['logFile'] = $logFile;

        if (is_readable($logFile)) {
            $data = file_get_contents($logFile);
            if(array_key_exists("disableFileProcessing",$config) && $config['disableFileProcessing']){
                return new \Symfony\Component\HttpFoundation\Response($data);
            }


            $fileSizeMb = round(filesize($logFile) / 1024 / 1024);

            if ($fileSizeMb < 100) {
                $data = file_get_contents($logFile);
                $data = explode("\n", $data);
            } else {
                $data = explode("\n", shell_exec('tail -n 1000 ' . $logFile));
                $warning = '<span style="color:#ff131c">The log file is to large to view all contents (' . $fileSizeMb.'MB). The last 1000 lines are displayed. File: ' . $logFile . '</span>';
                array_unshift($data, $warning);
                array_push($data, $warning);
            }

            foreach ($data as $i => $row) {
                if ($row) {
                    if (strpos($row, '.WARNING')) {
                        $data[$i] = '<span style="color:#ffb13b">'.$row.'</span>';
                    }
                    if (strpos($row, '.ERROR') || strpos($row, '.CRITICAL')) {
                        $data[$i] = '<span style="color:#ff131c">'.$row.'</span>';
                    }
                    if (strpos($row, 'dev-server > ') === 0 || strpos($row, 'production-server > ') === 0) {
                        $data[$i] = '<span style="color:#35ad33">'.$row.'</span>';
                    }
                    foreach (['[echo]', '[mkdir]', '[delete]', '[copy]'] as $k) {
                        if (strpos($row, $k)) {
                            $data[$i] = '<span style="color:#49b7d4">'.$row.'</span>';
                        }
                    }
                }
            }
        } else {
            $data = ["Log file doesn't exist. ".$logFile];
        }
        $data = implode("\n", $data);

        $viewData['data'] = $data;
        $viewData['monitoringItem'] = $monitoringItem;
        return $this->render('@ElementsProcessManager/MonitoringItem/logFileLogger.html.twig', $viewData);
    }

    /**
     * @Route("/delete")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $this->checkPermission('plugin_pm_permission_delete_monitoring_item');
        $entry = MonitoringItem::getById($request->get('id'));
        if ($entry) {
            if($entry->isAlive()){
                $entry->stopProcess();
            }
            $entry->delete();

            return $this->adminJson(['success' => true]);
        }

        return $this->adminJson(['success' => false, 'message' => "Couldn't delete entry"]);
    }

    /**
     * @Route("/delete-batch")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteBatchAction(Request $request)
    {
        $this->checkPermission('plugin_pm_permission_delete_monitoring_item');
        $logLevels = array_filter(explode(',', $request->get('logLevels')));
        if (!empty($logLevels)) {
            $list = new MonitoringItem\Listing();
            $conditions = [];
            foreach ($logLevels as $loglevel) {
                $conditions[] = ' status ="'.$loglevel.'" ';
            }
            $condition = implode(' OR ', $conditions);
            $list->setCondition($condition);
            $items = $list->load();
            foreach ($items as $item) {
                $item->delete();
            }

            return $this->adminJson(['success' => true]);
        } else {
            return $this->adminJson(
                [
                    'success' => false,
                    'message' => 'No statuses -> didn\'t deleted logs. Please select at least one status',
                ]
            );
        }
    }

    /**
     * @Route("/cancel")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function cancelAction(Request $request)
    {
        $monitoringItem = MonitoringItem::getById($request->get('id'));
        try {
            $pid = $monitoringItem->getPid();
            if ($pid) {
                $status = $monitoringItem->stopProcess();
                $message = 'Process with PID "'.$pid.'" killed by Backend User: '.$this->getUser()->getUser()->getName();
                $monitoringItem->getLogger()->warning($message);
                foreach($monitoringItem->getChildProcesses() as $child) {
                    $child->stopProcess();
                }
                return $this->adminJson(['success' => $status]);
            }

            return $this->adminJson(['success' => true]);
        } catch (\Exception $e) {
            return $this->adminJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/restart")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function restartAction(Request $request)
    {
        try {
            $monitoringItem = MonitoringItem::getById($request->get('id'));
            $monitoringItem->deleteLogFile()->resetState()->save();
            putenv(ElementsProcessManagerBundle::MONITORING_ITEM_ENV_VAR . '=' . $monitoringItem->getId());
            $pid = \Pimcore\Tool\Console::execInBackground($monitoringItem->getCommand(), $monitoringItem->getLogFile());
            $monitoringItem->setPid($pid)->save();
            return $this->adminJson(['success' => true,'PID' => $pid]);
        } catch (\Exception $e) {
            return $this->adminJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/get-by-id")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getByIdAction(Request $request){
        $data = [];

        $item = MonitoringItem::getById($request->get('id'));
        $data = $item->getObjectVars();
        $data['callbackSettings'] = json_decode($data['callbackSettings']);
        $data['executorSettings']['values'] = [];
        return $this->adminJson($data);

    }

}

<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Controller;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Enums;
use Elements\Bundle\ProcessManagerBundle\Executor\Action\AbstractAction;
use Elements\Bundle\ProcessManagerBundle\Executor\Logger\AbstractLogger;
use Elements\Bundle\ProcessManagerBundle\Executor\Logger\Application;
use Elements\Bundle\ProcessManagerBundle\Executor\Logger\File;
use Elements\Bundle\ProcessManagerBundle\Message\ExecuteCommandMessage;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Bundle\AdminBundle\Helper\QueryParams;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\Model\User;
use Pimcore\Tool\Text;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/elementsprocessmanager/monitoring-item')]
class MonitoringItemController extends UserAwareController
{
    use JsonHelperTrait;

    #[Route(path: '/list')]
    public function listAction(Request $request): JsonResponse
    {
        $this->checkPermission(Enums\Permissions::VIEW);
        $data = [];

        $list = new MonitoringItem\Listing();
        $list->setOrder('DESC');
        $list->setOrderKey('id');
        $list->setLimit($request->get('limit', 25));
        $list->setUser($this->getPimcoreUser());

        $list->setOffset($request->get('start', 0));

        $allParams = array_merge($request->request->all(), $request->query->all());
        $sortingSettings = QueryParams::extractSortingSettings($allParams);
        if ($sortingSettings['orderKey'] && $sortingSettings['order']) {
            $list->setOrderKey($sortingSettings['orderKey']);
            $list->setOrder($sortingSettings['order']);
        }

        $callbacks = [
            'executedByUser' => function ($f): string {
                $db = \Pimcore\Db::get();
                $ids = $db->fetchFirstColumn('SELECT id FROM users where name LIKE ' . $db->quote('%' . $f->value . '%')) ?: [0];

                return ' executedByUser IN( ' . implode(',', $ids) . ') ';
            },
        ];
        if ($filterCondition = QueryParams::getFilterCondition(
            $request->get('filter', ''),
            ['id', 'o_id', 'pid'],
            true,
            $callbacks
        )
        ) {
            $list->setCondition($filterCondition);
        }

        $condition = $list->getCondition();
        if ($filters = $request->get('filter')) {
            foreach (json_decode((string)$filters, true, 512, JSON_THROW_ON_ERROR) as $e) {
                if ($e['property'] == 'id') {
                    $condition .= ' OR `parentId` = ' . (int)$e['value'] . ' ';
                }
            }
        }

        if (!$request->get('showHidden') || $request->get('showHidden') == 'false') {
            $filterConditionArray = QueryParams::getFilterCondition($request->get('filter', ''), ['id', 'o_id', 'pid'], false, $callbacks);

            if ($filterConditionArray && isset($filterConditionArray['id'])) {
            } elseif ($condition !== '' && $condition !== '0') {
                $condition .= ' AND published=1';
            } else {
                $condition .= ' published=1';
            }
        }
        $list->setCondition($condition);

        $total = $list->getTotalCount();

        foreach ($list->load() as $item) {
            $data[] = $this->getItemData($item);
        }

        return $this->jsonResponse(['success' => true, 'total' => $total, 'data' => $data]);
    }

    #[Route(path: '/update')]
    public function update(Request $request): JsonResponse
    {

        $monitoringItem = MonitoringItem::getById($request->get('id'));

        $data = [];

        if ($monitoringItem) {
            if ($monitoringItem->getExecutedByUser() == $this->getPimcoreUser()->getId()) {
                $params = $request->request->all();
                if (isset($params['published']) && $params['published'] === 'false') {
                    $params['published'] = false;
                }
                foreach ($params as $key => $value) {
                    $setter = 'set' . ucfirst($key);
                    if (method_exists($monitoringItem, $setter)) {
                        $monitoringItem->$setter($value);
                    }
                }
                $monitoringItem->save();
            }
            $data = $this->getItemData($monitoringItem);
        }

        return $this->json(['success' => true, 'data' => $data]);

    }

    protected function getProcessesForCurrentUser(): MonitoringItem\Listing
    {
        $list = new MonitoringItem\Listing();
        $list->setOrder('DESC');
        $list->setOrderKey('id');
        //$list->setLimit(10);

        $list->setCondition('executedByUser = ? and parentId IS NULL AND published = 1 ', [$this->getPimcoreUser()->getId()]);

        return $list;
    }

    #[Route(path: '/update-all-user-monitoring-items')]
    public function updateAllUserMonitoringItems(Request $request): JsonResponse
    {

        $list = $this->getProcessesForCurrentUser();
        $params = $request->request->all();
        if (isset($params['published']) && $params['published'] === 'false') {
            $params['published'] = false;
        }
        /**
         * @var MonitoringItem $item
         */
        foreach ($list->load() as $item) {
            $item->setValues($params)->save();
        }

        return $this->jsonResponse(['success' => true]);
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/list-processes-for-user')]
    public function listProcessesForUser(): JsonResponse
    {
        $data = [
            'total' => 0,
            'active' => 0,
            'items' => [],
        ];

        try {
            $this->checkPermission(Enums\Permissions::VIEW);
        } catch (\Exception) {
            return $this->jsonResponse($data);
        }
        $list = $this->getProcessesForCurrentUser();

        $data['total'] = $list->getTotalCount();
        foreach ($list->load() as $item) {
            $tmp = $this->getItemData($item);
            if ($tmp['isAlive']) {
                $data['active']++;
            }
            $data['items'][] = $tmp;
        }

        return $this->jsonResponse($data);
    }

    /**
     * @param MonitoringItem $item
     *
     * @return array<string, mixed>
     *
     * @throws \JsonException
     */
    protected function getItemData(MonitoringItem $item): array
    {
        $tmp = $item->getObjectVars();
        $tmp['messageShort'] = Text::cutStringRespectingWhitespace($tmp['message'] ?? '', 30);
        $tmp['steps'] = '-';
        if ($item->getTotalSteps() > 0 || $item->getCurrentStep()) {
            $tmp['steps'] = $item->getCurrentStep() . '/' . $item->getTotalSteps();
        }
        $tmp['duration'] = $item->getDuration() ?: '-';
        $tmp['progress'] = 0;

        if ($tmp['executedByUser']) {
            $user = User::getById($tmp['executedByUser']);
            $tmp['executedByUser'] = $user instanceof \Pimcore\Model\User ? $user->getName() : 'User id: ' . $tmp['executedByUser'];
        } else {
            $tmp['executedByUser'] = 'System';
        }

        $logFile = 0;
        $tmp['action'] = '';

        if ($actions = $item->getActions()) {
            foreach ($actions as $action) {
                /**
                 * @var AbstractAction $class
                 */
                $class = new $action['class'];
                if (($s = $class->getGridActionHtml($item, $action)) !== '' && ($s = $class->getGridActionHtml($item, $action)) !== '0') {
                    $tmp['action'] .= $s;
                }
            }
        }
        $tmp['actionItems'] = [];

        if ($tmp['actions']) {
            $actionItems = $tmp['actions'];

            foreach ($actionItems as $i => $v) {
                if ($class = $v['class']) {
                    if (\Pimcore\Tool::classExists($class)) {
                        $o = new $class();
                        $v['dynamicData'] = $o->toJson($item, $v);
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
                 * @var AbstractLogger $class
                 */
                $class = new $logger['class'];
                if (\Pimcore\Tool::classExists($class::class)) {
                    $logger['index'] = $i;
                    if (($s = $class->getGridLoggerHtml($item, $logger)) !== '' && ($s = $class->getGridLoggerHtml($item, $logger)) !== '0') {
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
            $config = Configuration::getById($item->getConfigurationId() ?? '');
            if ($config instanceof \Elements\Bundle\ProcessManagerBundle\Model\Configuration) {
                if ($config->getActive() == 0) {
                    $tmp['retry'] = 0;
                } else {
                    $uniqueExecution = $config->getExecutorClassObject()->getValues()['uniqueExecution'] ?? false;
                    if ($uniqueExecution) {
                        $runningProcesses = $config->getRunningProcesses();
                        if ($runningProcesses !== []) {
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
            $tmp['progress'] = '<div class="x-progress x-progress-default x-border-box" style="width:100%;"><div class="x-progress-text x-progress-text-back" ></div><div class="x-progress-bar x-progress-bar-default" style="width:' . $progress . '%;min-width: 35px; "><div class="x-progress-text" style="text-align:left;margin-left: 5px;"><div>' . $progress . '%</div></div></div></div>';
        }

        $tmp['progressPercentage'] = (float)$item->getProgressPercentage();
        $tmp['callbackSettingsString'] = json_encode($item->getCallbackSettings(), JSON_THROW_ON_ERROR);
        $tmp['callbackSettings'] = $item->getCallbackSettingsForGrid();

        return $tmp;
    }

    #[Route(path: '/log-application-logger')]
    public function logApplicationLoggerAction(Request $request): JsonResponse
    {
        $config = [];

        try {
            $monitoringItem = MonitoringItem::getById($request->get('id'));

            if (!$monitoringItem) {
                throw new \Exception('Monitoring Item with id' . $request->get('id') . ' not found');
            }
            $loggerIndex = $request->get('loggerIndex');
            if ($loggers = $monitoringItem->getLoggers()) {
                foreach ((array)$loggers as $i => $config) {
                    /**
                     * @var AbstractLogger $class
                     */
                    $class = new $config['class'];
                    if (\Pimcore\Tool::classExists($class::class) && $i == $loggerIndex) {
                        /**
                         * @var Application $logger
                         */
                        $logger = $class;
                        if (!$config['logLevel']) {
                            $config['logLevel'] = 'DEBUG';
                        }

                        break;
                    }
                }
            }

            $result = $monitoringItem->getObjectVars();
            $result['logLevel'] = strtolower((string)$config['logLevel']);

            return $this->jsonResponse(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    #[Route(path: '/log-file-logger')]
    public function logFileLoggerAction(Request $request, ?Profiler $profiler): Response
    {
        $config = [];
        $logFile = null;
        if ($profiler instanceof \Symfony\Component\HttpKernel\Profiler\Profiler) {
            $profiler->disable();
        }
        $viewData = [];
        $monitoringItem = MonitoringItem::getById($request->get('id'));

        $loggerIndex = $request->get('loggerIndex');
        if ($loggers = $monitoringItem->getLoggers()) {
            foreach ((array)$loggers as $i => $config) {
                /**
                 * @var AbstractLogger $class
                 */
                $class = new $config['class'];
                if (\Pimcore\Tool::classExists($class::class) && $i == $loggerIndex) {
                    /**
                     * @var File $logger
                     */
                    $logger = $class;
                    $logFile = $logger->getLogFile($config, $monitoringItem);
                    if (!$config['logLevel']) {
                        $config['logLevel'] = 'DEBUG';
                    }

                    break;
                }
            }
        }
        $viewData['logLevel'] = $config['logLevel'];
        $viewData['logFile'] = $logFile;

        if (is_readable($logFile)) {
            $data = file_get_contents($logFile);
            if (array_key_exists('disableFileProcessing', $config) && $config['disableFileProcessing']) {
                return new Response($data);
            }

            $fileSizeMb = round(filesize($logFile) / 1024 / 1024);

            if ($fileSizeMb < 100) {
                $data = file_get_contents($logFile);
                $data = explode("\n", $data);
            } else {
                $data = explode("\n", shell_exec('tail -n 1000 ' . $logFile));
                $warning = '<span style="color:#ff131c">The log file is to large to view all contents (' . $fileSizeMb . 'MB). The last 1000 lines are displayed. File: ' . $logFile . '</span>';
                array_unshift($data, $warning);
                $data[] = $warning;
            }

            foreach ($data as $i => $row) {
                if ($row !== '' && $row !== '0') {
                    if (strpos($row, '.WARNING')) {
                        $data[$i] = '<span style="color:#ffb13b">' . $row . '</span>';
                    }
                    if (strpos($row, '.ERROR') || strpos($row, '.CRITICAL')) {
                        $data[$i] = '<span style="color:#ff131c">' . $row . '</span>';
                    }
                    if (str_starts_with($row, 'dev-server > ') || str_starts_with($row, 'production-server > ')) {
                        $data[$i] = '<span style="color:#35ad33">' . $row . '</span>';
                    }
                    foreach (['[echo]', '[mkdir]', '[delete]', '[copy]'] as $k) {
                        if (strpos($row, $k)) {
                            $data[$i] = '<span style="color:#49b7d4">' . $row . '</span>';
                        }
                    }
                }
            }
        } else {
            $data = ["Log file doesn't exist. " . $logFile];
        }
        $data = implode("\n", $data);

        $viewData['data'] = $data;
        $viewData['monitoringItem'] = $monitoringItem;

        if ($request->get('ajax')) {
            return new JsonResponse(['html' => $this->renderView('@ElementsProcessManager/MonitoringItem/logFileLogger.html.twig', $viewData),
                'monitoringItem' => $monitoringItem->getObjectVars()]);
        }
        return $this->render('@ElementsProcessManager/MonitoringItem/logFileLogger.html.twig', $viewData);
    }

    #[Route(path: '/delete')]
    public function deleteAction(Request $request): JsonResponse
    {
        $this->checkPermission('plugin_pm_permission_delete_monitoring_item');
        $entry = MonitoringItem::getById($request->get('id'));
        if ($entry) {
            if ($entry->isAlive()) {
                $entry->stopProcess();
            }
            $entry->delete();

            return $this->jsonResponse(['success' => true]);
        }

        return $this->jsonResponse(['success' => false, 'message' => "Couldn't delete entry"]);
    }

    #[Route(path: '/delete-batch')]
    public function deleteBatchAction(Request $request): JsonResponse
    {
        $this->checkPermission('plugin_pm_permission_delete_monitoring_item');
        $logLevels = array_filter(explode(',', (string)$request->get('logLevels')));
        if ($logLevels !== []) {
            $list = new MonitoringItem\Listing();
            $conditions = [];
            foreach ($logLevels as $loglevel) {
                $conditions[] = ' status ="' . $loglevel . '" ';
            }
            $condition = implode(' OR ', $conditions);
            $list->setCondition($condition);
            $items = $list->load();
            foreach ($items as $item) {
                $item->delete();
            }

            return $this->jsonResponse(['success' => true]);
        } else {
            return $this->jsonResponse(
                [
                    'success' => false,
                    'message' => 'No statuses -> didn\'t deleted logs. Please select at least one status',
                ]
            );
        }
    }

    #[Route(path: '/cancel')]
    public function cancelAction(Request $request): JsonResponse
    {
        $monitoringItem = MonitoringItem::getById($request->get('id'));

        try {
            $pid = $monitoringItem->getPid();
            if ($pid) {
                $status = $monitoringItem->stopProcess();
                $message = 'Process with PID "' . $pid . '" killed by Backend User: ' . $this->getPimcoreUser()->getName();
                $monitoringItem->getLogger()->warning($message);
                foreach ($monitoringItem->getChildProcesses() as $child) {
                    $child->stopProcess();
                }

                return $this->jsonResponse(['success' => $status]);
            }

            return $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    #[Route(path: '/restart')]
    public function restartAction(Request $request, MessageBusInterface $messageBus): JsonResponse
    {
        try {
            $monitoringItem = MonitoringItem::getById($request->get('id'));
            $monitoringItem->setMessengerPending(true);
            $monitoringItem->deleteLogFile()->resetState()->save();
            putenv(ElementsProcessManagerBundle::MONITORING_ITEM_ENV_VAR . '=' . $monitoringItem->getId());

            $message = new ExecuteCommandMessage($monitoringItem->getCommand(), $monitoringItem->getId(), $monitoringItem->getLogFile());
            $messageBus->dispatch($message);

            return $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    #[Route(path: '/get-by-id')]
    public function getByIdAction(Request $request): JsonResponse
    {
        $data = [];

        $item = MonitoringItem::getById($request->get('id'));
        $data = $item->getObjectVars();
        $data['executorSettings']['values'] = [];

        return $this->jsonResponse($data);

    }
}

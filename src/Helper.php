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

use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

class Helper
{
    public static function executeJob($configId, $callbackSettings = [], $userId = 0, $metaData = [], $parentMonitoringItemId = null, $callback = '')
    {
        try {
            $config = Configuration::getById($configId);

            $executor = $config->getExecutorClassObject();
            if ($executor->getValues()['uniqueExecution']  && is_null($parentMonitoringItemId)) {
                $running = $config->getRunningProcesses();
                if (!empty($running)) {
                    $msg = "Can't start the process because " . count($running) . ' process is running (ID: ' . $running[0]->getId() . '). Please wait until this processes is finished.';
                    throw new \Exception($msg);
                }
            }

            $monitoringItem = new MonitoringItem();
            $monitoringItem->setName($config->getName());
            $monitoringItem->setStatus($monitoringItem::STATUS_INITIALIZING);
            $monitoringItem->setConfigurationId($config->getId());
            $monitoringItem->setCallbackSettings($callbackSettings);
            $monitoringItem->setExecutedByUser($userId);
            $monitoringItem->setActions($executor->getActions());
            $monitoringItem->setLoggers($executor->getLoggers());
            $monitoringItem->setMetaData($metaData);
            $monitoringItem->setParentId($parentMonitoringItemId);

            if ($executorSettings = $config->getExecutorSettings()) {
                $executorData = json_decode($config->getExecutorSettings(), true);

                if ($executorData['values']) {
                    if ($executorData['values']['hideMonitoringItem'] == 'on') {
                        $monitoringItem->setPublished(false);
                    }
                    $monitoringItem->setGroup($executorData['values']['group']);
                }
            }

            if($callback){
                $callback($monitoringItem);
            }
            $item = $monitoringItem->save();

            $command = $executor->getCommand($callbackSettings, $monitoringItem);

            putenv(ElementsProcessManagerBundle::MONITORING_ITEM_ENV_VAR . '=' . $item->getId());

            if (!$executor->getIsShellCommand()) {
                $monitoringItem->getLogger()->info('Execution Command: ' . $command . ' in Background');
                $monitoringItem->setCommand($command)->save();
            } else {
                $monitoringItem->setCommand($command)->save();
                $command = $executor->getShellCommand($monitoringItem);

                $monitoringItem->getLogger()->info('Execution Command: ' . $command . ' in Background');
            }

            $pid = \Pimcore\Tool\Console::execInBackground($command);
            $monitoringItem->setPid($pid)->save();

            return ['success' => true, 'executedCommand' => $command, 'monitoringItemId' => $item->getId()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function getAllowedConfigIdsByUser(\Pimcore\Model\User $user)
    {
        if (!$user->isAdmin()) {
            $roles = $user->getRoles();
            if (empty($roles)) {
                $roles[] = 'no_result';
            }
            $c = [];
            foreach ($roles as $r) {
                $c[] = 'restrictToRoles LIKE "%,' . $r . ',%" ';
            }
            $c = ' (restrictToRoles = "" OR (' . implode(' OR ', $c) . ')) ';
            $ids = \Pimcore\Db::get()->fetchCol('SELECT id FROM ' . Configuration\Listing\Dao::getTableName() . ' WHERE ' . $c);

            return $ids;
        }
    }

    /**
     * @param MonitoringItem $monitoringItem
     *
     * @return MonitoringItem
     */
    public static function executeMonitoringItemLoggerShutdown(MonitoringItem $monitoringItem, $preventModificationDateUpdate = false){
        $loggers = $monitoringItem->getLoggers();

        foreach((array)$loggers as $i => $loggerConfig){
            $loggerClass = $loggerConfig['class'];
            if (!class_exists($loggerClass)) {
                continue;
            }
            $logObj = new $loggerClass;
            if(method_exists($logObj,'handleShutdown')){
                $result = $logObj->handleShutdown($monitoringItem,$loggerConfig);
                if($result){
                    $loggers[$i] = array_merge($loggerConfig,$result);
                }
            }
        }
        $monitoringItem->setLoggers($loggers)->save($preventModificationDateUpdate);
        return $monitoringItem;
    }

}

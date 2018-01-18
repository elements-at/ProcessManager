<?php

namespace Elements\Bundle\ProcessManagerBundle;

use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

class Helper
{


    public static function executeJob($configId, $callbackSettings = [], $userId = 0)
    {
        try {
            $config = Configuration::getById($configId);

            $executor = $config->getExecutorClassObject();
            if ($executor->getValues()['uniqueExecution']) {
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
            $item = $monitoringItem->save();

            $command = $executor->getCommand($callbackSettings, $monitoringItem);

            if (!$executor->getIsShellCommand()) {
                $command .= ' --monitoring-item-id=' . $item->getId();
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
            $ids = \Pimcore\Db::get()->fetchCol("SELECT id FROM " . Configuration\Listing\Dao::getTableName() . ' WHERE ' . $c);
            return $ids;
        }
    }
}
<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Executor\Action\AbstractAction;
use Elements\Bundle\ProcessManagerBundle\Model\Dao\AbstractDao;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Elements\Bundle\ProcessManagerBundle\Service\UploadManger;
use Pimcore\Db;

/**
 * Class Dao
 *
 */
class Dao extends AbstractDao
{
    /**
     * @var MonitoringItem|null
     *
     * @phpstan-ignore-next-line
     */
    protected $model = null;

    /**
     * @var null | Connection
     */
    protected static ?Connection $dbTransactionFree;

    public function getTableName(): string
    {
        return ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM;
    }

    /**
     * @param bool $preventModificationDateUpdate
     *
     * @throws Exception|\JsonException
     */
    public function save(bool $preventModificationDateUpdate = false): MonitoringItem|\Pimcore\Model\AbstractModel|null
    {
        if ($this->model->getIsDummy()) {
            return $this->model;
        }

        /**
         * If we are in a transction we need to create a new connection and use this because otherwise
         * the satus won't be updated as the transaction isn't committed (e.g when a exception is thrown within a transaction)
         */
        if (Db::get()->isTransactionActive()) {
            if (!self::$dbTransactionFree instanceof Connection) {
                self::$dbTransactionFree = DriverManager::getConnection($this->db->getParams());
            }
            $db = self::$dbTransactionFree;
        } else {
            $db = $this->db;
        }

        // refresh db connection if connection has been lost (happens when db connection has been idle for too long)
        //        if(false == $db->ping()) {
        //            $db = \Doctrine\DBAL\DriverManager::getConnection($this->db->getParams());
        //        }

        $data = $this->getValidStorageValues();
        if (!$preventModificationDateUpdate) {
            $data['modificationDate'] = time();
        }
        $quoteKeyData = [];
        array_walk($data, function ($value, $key) use (&$quoteKeyData): void {
            $quoteKeyData['`' . $key . '`'] = $value;
        });
        if (empty($quoteKeyData['`id`'])) {
            $db->insert($this->getTableName(), $quoteKeyData);
            $this->model->setId($db->lastInsertId($this->getTableName()));
        } else {
            $result = $db->update($this->getTableName(), $quoteKeyData, ['id' => $this->model->getId()]);
        }

        return $this->getById($this->model->getId());
    }

    public function delete(): void
    {
        $id = $this->model->getId();

        if ($id !== 0) {
            foreach ((array)$this->model->getActions() as $action) {
                if ($class = $action['class']) {
                    /**
                     * @var AbstractAction $class
                     */
                    $class = new $class();
                    $class->preMonitoringItemDeletion($this->model, $action);
                }
            }

            $this->db
                ->prepare('DELETE FROM ' . $this->getTableName() . ' WHERE `id` = ?')
                ->executeQuery([$id]);

            if ($logFile = $this->model->getLogFile()) {
                @unlink($logFile);
            }

            recursiveDelete(UploadManger::getUploadDir($id));

            $this->model = null;
        }
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    public function convertDataFromRecourse(array $data): array
    {
        if ($data['metaData']) {
            $data['metaData'] = json_decode($data['metaData'], true);
        }

        return $data;
    }
}

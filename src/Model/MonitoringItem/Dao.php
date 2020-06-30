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

namespace Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Executor\Action\AbstractAction;
use Elements\Bundle\ProcessManagerBundle\Model\Dao\AbstractDao;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

/**
 * Class Dao
 *
 */
class Dao extends AbstractDao
{
    /**
     * @var MonitoringItem
     */
    protected $model;

    /**
     * @var null | \Pimcore\Db\Connection
     */
    protected static $dbTransactionFree;

    public function getTableName()
    {
        return ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM;
    }

    /**
     * @param bool $preventModificationDateUpdate
     *
     * @return $this ->model
     */
    public function save($preventModificationDateUpdate = false)
    {
        if ($this->model->getIsDummy()) {
            return $this->model;
        }

        /**
         * If we are in a transction we need to create a new connection and use this because otherwise
         * the satus won't be updated as the transaction isn't committed (e.g when a exception is thrown within a transaction)
         */
        if(\Pimcore\Db::get()->isTransactionActive()){
            if(!self::$dbTransactionFree){
                self::$dbTransactionFree = \Doctrine\DBAL\DriverManager::getConnection($this->db->getParams());
            }
            $db = self::$dbTransactionFree;
        }else{
            $db = $this->db;
        }



        $data = $this->getValidStorageValues();
        if (!$preventModificationDateUpdate) {
            $data['modificationDate'] = time();
        }

        if (!$data['id']) {
            unset($data['id']);
            $db->insert($this->getTableName(), $data);
            $this->model->setId($db->lastInsertId($this->getTableName()));
        } else {
            $result = $db->update($this->getTableName(), $data, ['id' => $this->model->getId()]);
        }

        return $this->getById($this->model->getId());
    }

    public function delete()
    {
        if ($this->model->getId()) {
            foreach ((array)$this->model->getActions() as $action) {
                if ($class = $action['class']) {
                    /**
                     * @var AbstractAction $class
                     */
                    $class = new $class();
                    $class->preMonitoringItemDeletion($this->model, $action);
                }
            }

            if($this->model->isAlive()){
                $this->model->stopProcess();
            }

            $this->db->query('DELETE FROM '.$this->getTableName().' where id='.$this->model->getId());
            if ($logFile = $this->model->getLogFile()) {
                @unlink($logFile);
            }
            $this->model = null;
        }
    }
}

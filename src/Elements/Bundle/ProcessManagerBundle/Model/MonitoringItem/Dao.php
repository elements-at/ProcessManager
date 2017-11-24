<?php

namespace Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Model\Dao\AbstractDao;
use Elements\Bundle\ProcessManagerBundle\Executor\Action\AbstractAction;
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

    public function getTableName()
    {
        return ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM;
    }

    /**
     * @param bool $preventModificationDateUpdate
     * @return $this ->model
     */
    public function save($preventModificationDateUpdate = false)
    {
        if($this->model->getIsDummy()){
            return $this->model;
        }

        $data = $this->getValidStorageValues();
        if (!$preventModificationDateUpdate) {
            $data['modificationDate'] = time();
        }

        if (!$data['id']) {
            unset($data['id']);
            $this->db->insert($this->getTableName(), $data);
            $this->model->setId($this->db->lastInsertId($this->getTableName()));
        } else {
            $this->db->update($this->getTableName(), $data, array("id" => $this->model->getId()));
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
            $this->db->query("DELETE FROM ".$this->getTableName().' where id='.$this->model->getId());
            if ($logFile = $this->model->getLogFile()) {
                @unlink($logFile);
            }
            $this->model = null;
        }
    }
}

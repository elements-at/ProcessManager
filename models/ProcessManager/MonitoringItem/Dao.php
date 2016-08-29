<?php

namespace ProcessManager\MonitoringItem;

use Processmanager\Plugin;

/**
 * Class Dao
 *
 */
class Dao extends \ProcessManager\Dao\AbstractDao {
    /**
     * @var \ProcessManager\MonitoringItem
     */
    protected $model;

    public function getTableName(){
        return Plugin::TABLE_NAME_MONITORING_ITEM;
    }

    /**
     * @return $this->model
     */
    public function save($preventModificationDateUpdate = false)
    {

        $data = $this->getValidStorageValues();
        if(!$preventModificationDateUpdate){
            $data['modificationDate'] = time();
        }

        if(!$data['id']){
            unset($data['id']);
            $this->db->insert($this->getTableName(),$data);
            $this->model->setId($this->db->lastInsertId($this->getTableName()));
        }else{
            $this->db->update($this->getTableName(), $data, $this->db->quoteInto("id = ?", $this->model->getId()));
        }

        return $this->getById($this->model->getId());
    }

    public function delete(){
        if($this->model->getId()){
            foreach((array)$this->model->getActions() as $action){
                if($class = $action['class']){
                    /**
                     * @var \ProcessManager\Executor\Action\AbstractAction $class
                     */
                    $class = new $class();
                    $class->preMonitoringItemDeletion($this->model,$action);
                }
            }
            $this->db->query("DELETE FROM " . $this->getTableName().' where id='. $this->model->getId());
            if($logFile = $this->model->getLogFile()){
                @unlink($logFile);
            }
            $this->model = null;
        }
    }
}

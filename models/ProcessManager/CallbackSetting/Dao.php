<?php

namespace ProcessManager\CallbackSetting;

use Processmanager\Plugin;

/**
 * Class Dao
 *
 */
class Dao extends \ProcessManager\Dao\AbstractDao {
    /**
     * @var \ProcessManager\CallbackSettings
     */
    protected $model;

    public function getTableName(){
        return Plugin::TABLE_NAME_CALLBACK_SETTING;
    }

    /**
     * @return $this->model
     */
    public function save()
    {

        $data = $this->getValidStorageValues();
        if(!$data['modificationDate']){
            $data['modificationDate'] = time();
        }
        if(!$data['creationDate']){
            $data['creationDate'] = time();
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
            $this->db->query("DELETE FROM " . $this->getTableName().' where id='. $this->model->getId());
            $this->model = null;
        }
    }
}

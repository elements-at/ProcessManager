<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 21.06.2016
 * Time: 17:27
 */

namespace ProcessManager\Dao;

class AbstractDao extends \Pimcore\Model\Dao\AbstractDao {

    protected $validColumns = array();

    /**
     * Get the valid columns from the database
     *
     * @return void
     */
    public function init() {
        $this->validColumns = $this->getValidTableColumns($this->getTableName());
    }

    protected function getValidStorageValues(){
        $data = [];

        foreach ($this->model->getObjectVars() as $key => $value) {
            if (in_array($key, $this->validColumns)) {
                if (is_array($value) || is_object($value)) {
                    $value = serialize($value);
                } else  if(is_bool($value)) {
                    $value = (int)$value;
                }
                $data[$key] = $value;
            }
        }
        if(!$data['creationDate']){
            $data['creationDate'] = time();
        }
        if(!$data['modificationDate']){
            $data['modificationDate'] = time();
        }
        return $data;
    }

    public function getById($id){
        $data = $this->db->fetchRow("SELECT * FROM " . $this->getTableName() . " WHERE id= " . (int)$id);
        if(!$data){
            return null;
        }
        $this->model->setValues($data);
        return $this->model;
    }
}
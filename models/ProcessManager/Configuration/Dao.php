<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 21.06.2016
 * Time: 14:32
 */

namespace ProcessManager\Configuration;
use Pimcore\Model;
use ProcessManager\MonitoringItem;
use \ProcessManager\Plugin;

class Dao extends \ProcessManager\Dao\AbstractDao {
    /**
     * @var \ProcessManager\Configuration
     */
    protected $model;

    protected function getTableName(){
        return Plugin::TABLE_NAME_CONFIGURATION;
    }

    public function delete(){
        if($this->model->getId()){

            $list = new MonitoringItem\Listing();
            $list->setCondition('configurationId = ?',[$this->model->getId()]);
            $items = $list->load();
            foreach($items as $item){
                $item->delete();
            }
            $this->db->query("DELETE FROM " . $this->getTableName().' where id='. $this->model->getId());
        }
    }

    /**
     * @return $this->model
     */
    public function save()
    {
        $data = $this->getValidStorageValues();
        if(!$data['id']){
            unset($data['id']);
            $this->db->insert($this->getTableName(),$data);
            $this->model->setId($this->db->lastInsertId($this->getTableName()));
        }else{
            $this->db->update($this->getTableName(), $data, $this->db->quoteInto("id = ?", $this->model->getId()));
        }

        return $this->getById($this->model->getId());
    }

    public function getById($id){
        $model = parent::getById($id);
        if($model){
            /**
             * @var $class \ProcessManager\Executor\AbstractExecutor
             */
            $className = $model->getExecutorClass();
            if($className){
                $class = new $className;
                $class->setDataFromResource($model);
            }
            return $model;
        }
    }


}
<?php

namespace ProcessManager\MonitoringItem\Listing;

use ProcessManager\Plugin;
use Pimcore\Model;

class Dao extends Model\Listing\Dao\AbstractDao {

    protected function getTableName(){
        return Plugin::TABLE_NAME_MONITORING_ITEM;
    }

    /**
     * @return string
     */
    protected function getCondition()
    {
        $condition = '';
        if ($cond = $this->model->getCondition()) {
            $condition .= " WHERE " . $cond . " ";
        }

        /**
         * @var \Pimcore\Model\User $user
         */
        if($user = $this->model->getUser()){
            if(!$user->isAdmin()){
                if($ids = \ProcessManager\Helper::getAllowedConfigIdsByUser($user)){
                    if($this->model->getCondition()){
                        $condition .= ' AND ';
                    }else{
                        $condition .= ' WHERE ';
                    }
                    $condition .= ' configurationId IN(' . implode(', ',$ids).')';
                }
            }
        }
        return $condition;
    }

    public function load() {
        $sql = "SELECT id FROM " . $this->getTableName() . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit();
        $ids = $this->db->fetchCol($sql,  $this->model->getConditionVariables());

        $items = [];
        foreach ($ids as $id) {
            $items[] = \ProcessManager\MonitoringItem::getById($id);
        }
        return $items;
    }

    public function getTotalCount() {
        return (int) $this->db->fetchOne("SELECT COUNT(*) as amount FROM " . $this->getTableName() . " ". $this->getCondition(), $this->model->getConditionVariables());
    }
}

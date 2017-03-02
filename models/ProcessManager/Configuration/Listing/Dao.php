<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 21.06.2016
 * Time: 15:00
 */

namespace ProcessManager\Configuration\Listing;

use ProcessManager\Plugin;
use Pimcore\Model;

class Dao extends Model\Listing\Dao\AbstractDao
{

    public static function getTableName()
    {
        return Plugin::TABLE_NAME_CONFIGURATION;
    }

    /**
     * @return array
     */
    public function load()
    {
        $items = [];

        $ids = $this->loadIdList();
        foreach ($ids as $id) {
            $items[] = \ProcessManager\Configuration::getById($id);
        }
        return $items;
    }

    public function getTotalCount()
    {
        return (int)$this->db->fetchOne("SELECT COUNT(*) as amount FROM " . $this->getTableName() . " " . $this->getCondition(), $this->model->getConditionVariables());
    }

    public function loadIdList()
    {
        $condition = $this->getCondition();
        if($user = $this->model->getUser()){
            if($ids = \ProcessManager\Helper::getAllowedConfigIdsByUser($user)){
                if($condition) {
                    $condition .= ' AND ';
                }else{
                    $condition .= ' WHERE ';
                }
                $condition .= ' id IN(' . implode(',',$ids).')';
            }
        }

        return $this->db->fetchCol("SELECT id FROM " . $this->getTableName() . $condition . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());
    }
}

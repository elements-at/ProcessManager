<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Model\Configuration\Listing;

use Doctrine\DBAL\Exception;
use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Helper;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration\Listing;
use Pimcore\Model;

/**
 * @property Listing $model
 */
class Dao extends Model\Listing\Dao\AbstractDao
{
    public static function getTableName(): string
    {
        return ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION;
    }

    /**
     * @return array<mixed>
     *
     * @throws Exception
     */
    public function load(): array
    {
        $items = [];

        $ids = $this->loadIdList();
        foreach ($ids as $id) {
            $items[] = Configuration::getById($id);
        }

        $this->model->setData($items);

        return $items;
    }

    public function getTotalCount(): int
    {
        return (int)$this->db->fetchOne('SELECT COUNT(*) as amount FROM ' . static::getTableName() . ' ' . $this->getCondition(), $this->model->getConditionVariables());
    }

    /**
     * @return array<mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function loadIdList(): array
    {
        $condition = $this->getCondition();
        $conditionVariables = $this->model->getConditionVariables();
        $types = [];

        if ($user = $this->model->getUser()) {
            $ids = Helper::getAllowedConfigIdsByUser($user);
            $condition .= $condition !== '' && $condition !== '0' ? ' AND ' : ' WHERE ';
            if ($ids) {
                $condition .= ' id IN('. implode(',', wrapArrayElements($ids, "'")).')';
            } else {
                $condition .= 'id IS NULL';
            }
        }

        return $this->db->fetchFirstColumn('SELECT id FROM ' . static::getTableName() . $condition . $this->getOrder() . $this->getOffsetLimit(), $conditionVariables, $types);
    }
}

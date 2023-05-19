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

namespace Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem\Listing;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Helper;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Model;

class Dao extends Model\Listing\Dao\AbstractDao
{
    protected function getTableName()
    {
        return ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM;
    }

    /**
     * @return string
     */
    protected function getCondition()
    {
        $condition = '';
        if ($cond = $this->model->getCondition()) {
            $condition .= ' WHERE ' . $cond . ' ';
        }

        /**
         * @var \Pimcore\Model\User $user
         */
        if ($user = $this->model->getUser()) {
            if (!$user->isAdmin()) {
                if ($ids = Helper::getAllowedConfigIdsByUser($user)) {
                    if ($this->model->getCondition()) {
                        $condition .= ' AND ';
                    } else {
                        $condition .= ' WHERE ';
                    }
                    $condition .= ' configurationId IN(' . implode(', ', wrapArrayElements($ids, "'")).')';
                }
            }
        }

        return $condition;
    }

    public function load()
    {
        $sql = 'SELECT id FROM ' . $this->getTableName() . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit();
        $ids = $this->db->fetchFirstColumn($sql, $this->model->getConditionVariables());

        $items = [];
        foreach ($ids as $id) {
            $item = MonitoringItem::getById($id);
            if($item) {//hack because somehow it can happen that we dont get a monitoring id if we are using multiprocessing and the element would be empty
                $items[] = $item;
            }
        }

        $this->model->setData($items);

        return $items;
    }

    public function getTotalCount()
    {
        return (int) $this->db->fetchOne('SELECT COUNT(*) as amount FROM ' . $this->getTableName() . ' '. $this->getCondition(), $this->model->getConditionVariables());
    }
}

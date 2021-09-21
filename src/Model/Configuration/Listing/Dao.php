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

namespace Elements\Bundle\ProcessManagerBundle\Model\Configuration\Listing;

use Doctrine\DBAL\Connection;
use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Helper;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Pimcore\Model;

class Dao extends Model\Listing\Dao\AbstractDao
{
    public static function getTableName()
    {
        return ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION;
    }

    /**
     * @return array
     */
    public function load()
    {
        $items = [];

        $ids = $this->loadIdList();
        foreach ($ids as $id) {
            $items[] = Configuration::getById($id);
        }

        return $items;
    }

    public function getTotalCount()
    {
        return (int)$this->db->fetchOne('SELECT COUNT(*) as amount FROM ' . $this->getTableName() . ' ' . $this->getCondition(), $this->model->getConditionVariables());
    }

    public function loadIdList()
    {
        $condition = $this->getCondition();
        $conditionVariables = $this->model->getConditionVariables();
        $types = [];
        if ($user = $this->model->getUser()) {
            if ($ids = Helper::getAllowedConfigIdsByUser($user)) {
                if ($condition) {
                    $condition .= ' AND ';
                } else {
                    $condition .= ' WHERE ';
                }
                $condition .= ' id IN(:ids)';
                $conditionVariables["ids"] = $ids;
                $types["ids"] = Connection::PARAM_STR_ARRAY;
            }
        }

        return $this->db->fetchCol('SELECT id FROM ' . $this->getTableName() . $condition . $this->getOrder() . $this->getOffsetLimit(), $conditionVariables,$types);
    }
}

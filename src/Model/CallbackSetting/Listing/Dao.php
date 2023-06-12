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

namespace Elements\Bundle\ProcessManagerBundle\Model\CallbackSetting\Listing;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Model\CallbackSetting;
use Pimcore\Model;

class Dao extends Model\Listing\Dao\AbstractDao
{
    protected function getTableName()
    {
        return ElementsProcessManagerBundle::TABLE_NAME_CALLBACK_SETTING;
    }

    public function load(): array
    {
        $sql = 'SELECT id FROM '.$this->getTableName().$this->getCondition().$this->getOrder().$this->getOffsetLimit();
        $ids = $this->db->fetchFirstColumn($sql, $this->model->getConditionVariables());

        $items = [];
        foreach ($ids as $id) {
            $items[] = CallbackSetting::getById($id);
        }

        return $items;
    }

    public function getTotalCount(): int
    {
        return (int)$this->db->fetchOne(
            'SELECT COUNT(*) as amount FROM '.$this->getTableName().' '.$this->getCondition(),
            $this->model->getConditionVariables()
        );
    }
}

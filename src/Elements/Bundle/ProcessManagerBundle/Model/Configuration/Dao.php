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

namespace Elements\Bundle\ProcessManagerBundle\Model\Configuration;

use Elements\Bundle\ProcessManagerBundle\AbstractExecutor;
use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

class Dao extends \Elements\Bundle\ProcessManagerBundle\Model\Dao\AbstractDao
{
    /**
     * @var Configuration
     */
    protected $model;

    protected function getTableName()
    {
        return ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION;
    }

    public function delete()
    {
        if ($this->model->getId()) {
            $list = new MonitoringItem\Listing();
            $list->setCondition('configurationId = ?', [$this->model->getId()]);
            $items = $list->load();
            foreach ($items as $item) {
                $item->delete();
            }
            $this->db->query('DELETE FROM ' . $this->getTableName().' where id='. $this->model->getId());
        }
    }

    /**
     * @return $this->model
     */
    public function save()
    {
        $data = $this->getValidStorageValues();
        if (!$data['id']) {
            unset($data['id']);
            $this->db->insert($this->getTableName(), $data);
            $this->model->setId($this->db->lastInsertId($this->getTableName()));
        } else {
            $this->db->update($this->getTableName(), $data, ['id' => $this->model->getId()]);
        }

        return $this->getById($this->model->getId());
    }

    public function getById($id)
    {
        $model = parent::getById($id);
        if ($model) {
            /**
             * @var $class AbstractExecutor
             */
            $className = $model->getExecutorClass();
            if ($className) {
                $class = new $className;
                $class->setDataFromResource($model);
            }

            return $model;
        }
    }
}

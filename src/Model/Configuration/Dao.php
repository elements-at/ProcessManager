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

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Executor\AbstractExecutor;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\Dao\AbstractDao;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

class Dao extends AbstractDao
{
    /**
     * @var Configuration
     */
    protected $model;

    protected function getTableName(): string
    {
        return ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION;
    }

    public function delete(): void
    {
        $id = $this->model->getId();

        if (!is_null($id)) {
            $items = (new MonitoringItem\Listing())
                ->setCondition('configurationId = ?', [$id])
                ->load();

            foreach ($items as $item) {
                $item->delete();
            }

            $this->db
                ->prepare('DELETE FROM ' . $this->getTableName() . ' WHERE `id` = ?')
                ->executeQuery([$id]);
        }
    }

    /**
     * @return $this->model
     */
    public function save($params = [])
    {
        $data = $this->getValidStorageValues();

        if ($data['keepVersions'] === '') {
            $data['keepVersions'] = null;
        }
        if (!$data['id']) {
            throw new \Exception('A valid Command has to have an id associated with it!');
        }

        $quoteKeyData= [];
        array_walk($data, function ($value, $key) use (&$quoteKeyData) { $quoteKeyData['`'.$key.'`'] = $value; });

        if (isset($params['oldId'])) {
            if ($params['oldId'] != '') {
                $this->db->update($this->getTableName(), $quoteKeyData, ['id' => $params['oldId']]);
            } else {
                $this->db->insert($this->getTableName(), $quoteKeyData);
            }
        } else {
            if ($id = $this->getById($id = $this->model->getId())) {
                $this->db->update($this->getTableName(), $quoteKeyData, ['id' => $this->model->getId()]);
            } else {
                $this->db->insert($this->getTableName(), $quoteKeyData);
            }
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

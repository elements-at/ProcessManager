<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Model\CallbackSetting;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Model\CallbackSetting;
use Elements\Bundle\ProcessManagerBundle\Model\Dao\AbstractDao;

/**
 * @property CallbackSetting|null $model
 */
class Dao extends AbstractDao
{
    public function getTableName(): string
    {
        return ElementsProcessManagerBundle::TABLE_NAME_CALLBACK_SETTING;
    }

    public function save(): ?CallbackSetting
    {
        $data = $this->getValidStorageValues();
        if (!$data['modificationDate']) {
            $data['modificationDate'] = time();
        }
        if (!$data['creationDate']) {
            $data['creationDate'] = time();
        }
        if (empty($data['id'])) {
            $this->db->insert($this->getTableName(), $data);
            $this->model->setId($this->db->lastInsertId($this->getTableName()));
        } else {
            $this->db->update($this->getTableName(), $data, ['id' => $this->model->getId()]);
        }

        /**
         * @var CallbackSetting $model
         */
        $model = $this->getById($this->model->getId());

        return $model;
    }

    public function delete(): void
    {
        $id = $this->model->getId();

        if ($id !== 0) {
            $this->db
                ->prepare('DELETE FROM ' . $this->getTableName() . ' WHERE `id` = ?')
                ->executeQuery([$id]);

            $this->model = null;
        }
    }
}

<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Model\Dao;

use Doctrine\DBAL\Exception;
use Pimcore\Model\AbstractModel;

abstract class AbstractDao extends \Pimcore\Model\Dao\AbstractDao
{
    /**
     * @var array<mixed>
     */
    protected array $validColumns = [];

    /**
     * Get the valid columns from the database
     *
     * @return void
     */
    public function init(): void
    {

        $tableName = $this->getTableName();
        $this->validColumns = $this->getValidTableColumns($tableName);
    }

    abstract protected function getTableName(): string;

    /**
     * @return array<mixed>
     */
    protected function getValidStorageValues(): array
    {
        $data = [];
        foreach ($this->model->getObjectVars() as $key => $value) {
            if (in_array($key, $this->validColumns)) {
                if (is_object($value)) {
                    $value = $value::class;
                } elseif(is_array($value)) {
                    foreach($value as $k => $v) {
                        if(is_object($v) && method_exists($v, 'getStorageData')) {
                            $value[$k] = $v->getStorageData();
                        }
                    }
                    $value = json_encode($value, JSON_THROW_ON_ERROR);

                } elseif (is_bool($value)) {
                    $value = (int)$value;
                }
                $data[$key] = $value;
            }
        }
        if (empty($data['creationDate'])) {
            $data['creationDate'] = time();
        }
        if (empty($data['modificationDate'])) {
            $data['modificationDate'] = time();
        }

        return $data;
    }

    /**
     * @return AbstractModel|null
     *
     * @throws Exception
     */
    public function getById(mixed $id): ?AbstractModel
    {
        $data = $this->db->fetchAssociative('SELECT * FROM ' . $this->getTableName() . ' WHERE id= :id', ['id' => $id]);
        if (!$data) {
            return null;
        }
        $data = $this->convertDataFromRecourse($data);
        $this->model->setValues($data);

        return $this->model;
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    public function convertDataFromRecourse(array $data): array
    {
        return $data;
    }
}

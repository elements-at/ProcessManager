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

namespace Elements\Bundle\ProcessManagerBundle\Model\Dao;

class AbstractDao extends \Pimcore\Model\Dao\AbstractDao
{
    protected $validColumns = [];

    /**
     * Get the valid columns from the database
     *
     * @return void
     */
    public function init()
    {
        $tableName = $this->getTableName();
        $this->validColumns = $this->getValidTableColumns($tableName);
    }

    protected function getValidStorageValues()
    {
        $data = [];
        foreach ($this->model->getObjectVars() as $key => $value) {
            if (in_array($key, $this->validColumns)) {
                if (is_object($value)) {
                    $value = get_class($value);
                }elseif(is_array($value)){
                    foreach($value as $k => $v){
                        if(is_object($v)){
                            if(method_exists($v,'getStorageData')){
                                $value[$k] = $v->getStorageData();
                            }
                        }
                    }
                    $value = json_encode($value);

                } elseif (is_bool($value)) {
                    $value = (int)$value;
                }
                $data[$key] = $value;
            }
        }
        if (!$data['creationDate']) {
            $data['creationDate'] = time();
        }
        if (!$data['modificationDate']) {
            $data['modificationDate'] = time();
        }

        return $data;
    }

    public function getById($id)
    {
        $data = $this->db->fetchRow('SELECT * FROM ' . $this->getTableName() . ' WHERE id= ' . (int)$id);
        if (!$data) {
            return null;
        }
        $data['id'] = (int)$data['id'];
        $this->model->setValues($data);

        return $this->model;
    }
}

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
 * @copyright  Copyright (c) elements.at New Media Solutions GmbH (https://www.elements.at)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Elements\Bundle\ProcessManagerBundle;

use Symfony\Component\Filesystem\Filesystem;

class MetaDataFile
{
    /**
     * @var array
     */
    protected static $instances = [];

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var array
     */
    protected $data;

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     *
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    protected static function getFile($identifier)
    {
        $datFile = PIMCORE_PRIVATE_VAR . '/process-manager-meta-data-files/';
        $filesystem = new Filesystem();
        $filesystem->mkdir($datFile, 0775);
        $datFile .= "$identifier.json";

        return $datFile;
    }

    /**
     * Unique identifier for the file
     *
     * @param $identifier
     *
     * @return static
     */
    public static function getById($identifier)
    {
        if (isset(self::$instances[$identifier]) == false) {
            $tmp = new static();
            $tmp->setIdentifier($identifier);

            $file = self::getFile($identifier);
            if (file_exists($file)) {
                $data = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            } else {
                $data = [];
            }
            $tmp->setData($data);
            self::$instances[$identifier] = $tmp;
        }

        return self::$instances[$identifier];
    }

    public function delete()
    {
        $file = self::getFile($this->getIdentifier());
        if(is_file($file)) {
            @unlink($file);
        }
    }

    public function save()
    {
        $data = $this->getData();
        if (empty($data)) {
            throw new \Exception('No data to save ');
        }
        $check = file_put_contents(self::getFile($this->getIdentifier()), json_encode($this->getData(), JSON_PRETTY_PRINT));

        if (!$check) {
            throw new \Exception("Can't write file: " . $this->getIdentifier());
        }
    }
}

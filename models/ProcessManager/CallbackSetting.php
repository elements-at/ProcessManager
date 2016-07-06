<?php
namespace ProcessManager;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Class MonitoringItem
 *
 * @method  MonitoringItem save() \ProcessManager\MonitoringItem
 * @method  MonitoringItem load() []\ProcessManager\MonitoringItem
 */
class CallbackSetting extends \Pimcore\Model\AbstractModel {

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $settings;

    /**
     * @var string
     */
    public $type;

    /**
     * @var int
     */
    public $creationDate;

    /**
     * @var int
     */
    public $modificationDate;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param string $settings
     * @return $this
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
        return $this;
    }


    /**
     * @param integer $id
     * @return self
     */
    public static function getById($id) {
        $self = new self();
        return $self->getDao()->getById($id);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type 
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Model;

/**
 * Class MonitoringItem
 *
 * @method  CallbackSetting save() CallbackSetting
 * @method  CallbackSetting delete()
 * @method  CallbackSetting[] load()
 * @method CallbackSetting\Dao getDao()
 */
class CallbackSetting extends \Pimcore\Model\AbstractModel
{
    public ?int $id;

    public string $name = '';

    public string $description = '';

    public string $settings;

    public string $type;

    public int $creationDate;

    public int $modificationDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSettings(): string
    {
        return $this->settings;
    }

    public function setSettings(string $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public static function getById(int $id): ?self
    {
        $self = new self();
        /**
         * @var CallbackSetting|null $model
         */
        $model = $self->getDao()->getById($id);

        return $model;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}

<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle;

use Symfony\Component\Filesystem\Filesystem;

class MetaDataFile
{
    /**
     * @var MetaDataFile[]
     */
    protected static array $instances = [];

    protected string $identifier;

    /**
     * @var array<mixed>
     */
    protected array $data;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<mixed> $data
     *
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    protected static function getFile(string $identifier): string
    {
        $datFile = PIMCORE_PRIVATE_VAR . '/process-manager-meta-data-files/';
        $filesystem = new Filesystem();
        $filesystem->mkdir($datFile, 0775);

        return $datFile . "$identifier.json";
    }

    final public function __construct()
    {
    }

    /**
     * Unique identifier for the file
     *
     * @param string $identifier
     *
     * @throws \JsonException
     */
    public static function getById(string $identifier): self
    {
        if (!isset(self::$instances[$identifier])) {

            $tmp = new static();
            $tmp->setIdentifier($identifier);

            $file = self::getFile($identifier);
            $data = file_exists($file) ? json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR) : [];
            $tmp->setData($data);
            self::$instances[$identifier] = $tmp;
        }

        return self::$instances[$identifier];
    }

    public function delete(): void
    {
        $file = self::getFile($this->getIdentifier());
        if(is_file($file)) {
            @unlink($file);
        }
    }

    /**
     * @throws \Exception
     */
    public function save(): void
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

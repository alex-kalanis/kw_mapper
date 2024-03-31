<?php

namespace kalanis\kw_mapper\Storage\Storage;


use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\Storage as Store;
use kalanis\kw_storage\StorageException;


/**
 * Class StorageSingleton
 * @package kalanis\kw_mapper\Storage\Storage
 * Singleton to access storage across the mappers
 */
class StorageSingleton
{
    protected static ?StorageSingleton $instance = null;
    private Store\Factory $factory;
    private ?IStorage $storage = null;

    public static function getInstance(): self
    {
        if (empty(static::$instance)) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    protected function __construct()
    {
        $this->factory = new Store\Factory(new Store\Key\Factory(), new Store\Target\Factory());
    }

    /**
     * @codeCoverageIgnore why someone would run that?!
     */
    private function __clone()
    {
    }

    /**
     * @param object|array<string, string|object>|string $storageParams
     * @throws StorageException
     * @return IStorage
     */
    public function getStorage($storageParams): IStorage
    {
        if (empty($this->storage)) {
            if (empty($storageParams)) {
                throw new StorageException('Storage cannot be empty!');
            }
            $this->storage = $this->factory->getStorage($storageParams);
        }
        return $this->storage;
    }

    public function clearStorage(): void
    {
        $this->storage = null;
    }
}

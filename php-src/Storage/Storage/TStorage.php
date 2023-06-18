<?php

namespace kalanis\kw_mapper\Storage\Storage;


use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\StorageException;


/**
 * Trait TStorage
 * @package kalanis\kw_mapper\Storage\Storage
 */
trait TStorage
{
    /**
     * @param object|array<string, string|object>|string $storageParams
     * @throws StorageException
     * @return IStorage
     */
    protected function getStorage($storageParams = 'volume'): IStorage
    {
        return StorageSingleton::getInstance()->getStorage($storageParams);
    }

    protected function clearStorage(): void
    {
        StorageSingleton::getInstance()->clearStorage();
    }
}

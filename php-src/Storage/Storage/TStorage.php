<?php

namespace kalanis\kw_mapper\Storage\Storage;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\StorageException;


/**
 * Trait TStorage
 * @package kalanis\kw_mapper\Storage\Storage
 */
trait TStorage
{
    /**
     * Set another storage than usually called local volume
     * @param object|array<string, string|object>|string $storageParams
     * @throws MapperException
     */
    protected function setStorage($storageParams = 'volume'): void
    {
        try {
            $instance = StorageSingleton::getInstance();
            $instance->clearStorage();
            $instance->getStorage($storageParams);
        } catch (StorageException $ex) {
            throw new MapperException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @throws MapperException
     * @return IStorage
     */
    protected function getStorage(): IStorage
    {
        try {
            return StorageSingleton::getInstance()->getStorage('volume');
            // @codeCoverageIgnoreStart
        } catch (StorageException $ex) {
            // means you have failed storage - larger problem than "only" unknown storage
            throw new MapperException($ex->getMessage(), $ex->getCode(), $ex);
        }
        // @codeCoverageIgnoreEnd
    }

    protected function clearStorage(): void
    {
        StorageSingleton::getInstance()->clearStorage();
    }
}

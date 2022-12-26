<?php

namespace kalanis\kw_mapper\Storage\Storage;


use kalanis\kw_storage\Interfaces\IStorage;


/**
 * Trait TStorage
 * @package kalanis\kw_mapper\Storage\Storage
 */
trait TStorage
{
    protected function getStorage(): IStorage
    {
        return StorageSingleton::getInstance()->getStorage();
    }
}

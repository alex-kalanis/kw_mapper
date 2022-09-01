<?php

namespace kalanis\kw_mapper\Storage\File;


use kalanis\kw_storage\Interfaces\IStorage;


/**
 * Trait TStorage
 * @package kalanis\kw_mapper\Storage\File
 */
trait TStorage
{
    protected function getStorage(): IStorage
    {
        return StorageSingleton::getInstance()->getStorage();
    }
}

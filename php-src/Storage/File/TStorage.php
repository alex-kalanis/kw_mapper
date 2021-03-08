<?php

namespace kalanis\kw_mapper\Storage\File;


use kalanis\kw_storage\Storage\Storage;


/**
 * Trait TStorage
 * @package kalanis\kw_mapper\Storage\File§
 */
trait TStorage
{
    /**
     * @return Storage
     */
    protected function getStorage(): Storage
    {
        return StorageSingleton::getInstance()->getStorage();
    }
}

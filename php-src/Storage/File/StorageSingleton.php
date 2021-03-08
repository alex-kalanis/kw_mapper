<?php

namespace kalanis\kw_mapper\Storage\File;


use kalanis\kw_storage\Storage;


/**
 * Class StorageSingleton
 * @package kalanis\kw_mapper\Storage\File
 * Singleton to access storage across the mappers
 */
class StorageSingleton
{
    protected static $instance = null;
    private $storage = null;

    public static function getInstance(): self
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    protected function __construct()
    {
    }

    /**
     * @codeCoverageIgnore why someone would run that?!
     */
    private function __clone()
    {
    }

    public function getStorage(): Storage\Storage
    {
        if (empty($this->storage)) {
            $this->storage = new Storage\Storage(
                new Storage\Target\Volume(),
                new Storage\Format\Raw(),
                new Storage\Key\DefaultKey()
            );
        }
        return $this->storage;
    }
}

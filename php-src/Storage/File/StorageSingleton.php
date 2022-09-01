<?php

namespace kalanis\kw_mapper\Storage\File;


use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\Storage;


/**
 * Class StorageSingleton
 * @package kalanis\kw_mapper\Storage\File
 * Singleton to access storage across the mappers
 */
class StorageSingleton
{
    /** @var self|null */
    protected static $instance = null;
    /** @var IStorage|null */
    private $storage = null;

    public static function getInstance(): self
    {
        if (empty(static::$instance)) {
            static::$instance = new self();
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

    public function getStorage(): IStorage
    {
        if (empty($this->storage)) {
            $this->storage = new Storage\Storage(
                new Storage\Key\DefaultKey(),
                new Storage\Target\Volume()
            );
        }
        return $this->storage;
    }
}

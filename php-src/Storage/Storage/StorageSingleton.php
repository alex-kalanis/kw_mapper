<?php

namespace kalanis\kw_mapper\Storage\Storage;


use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\Storage as Store;


/**
 * Class StorageSingleton
 * @package kalanis\kw_mapper\Storage\Storage
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
            $this->storage = new Store\Storage(
                new Store\Key\DefaultKey(),
                new Store\Target\Volume()
            );
        }
        return $this->storage;
    }
}

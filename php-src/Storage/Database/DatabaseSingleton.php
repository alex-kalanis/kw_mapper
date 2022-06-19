<?php

namespace kalanis\kw_mapper\Storage\Database;


use kalanis\kw_mapper\MapperException;


/**
 * Class DatabaseSingleton
 * @package kalanis\kw_mapper\Storage\Database
 * Singleton to access databases across the mappers
 */
class DatabaseSingleton
{
    /** @var self|null */
    protected static $instance = null;
    /** @var ADatabase[] */
    private $database = [];

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

    /**
     * @param Config $config
     * @throws MapperException
     * @return ADatabase
     */
    final public function getDatabase(Config $config): ADatabase
    {
        if (empty($this->database[$config->getDriver()])) {
            $this->database[$config->getDriver()] = $this->getFactory()->getDatabase($config);
        }
        return $this->database[$config->getDriver()];
    }

    protected function getFactory(): Factory
    {
        return Factory::getInstance();
    }
}

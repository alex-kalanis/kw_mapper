<?php

namespace kalanis\kw_mapper\Storage\Database;


use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\MapperException;


/**
 * Class Factory
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 */
class Factory
{
    protected static $map = [
        IDriverSources::TYPE_PDO_MYSQL => '\kalanis\kw_mapper\Storage\Database\PDO\MySQL',
        IDriverSources::TYPE_PDO_MSSQL => '\kalanis\kw_mapper\Storage\Database\PDO\MSSQL',
        IDriverSources::TYPE_PDO_ORACLE => '\kalanis\kw_mapper\Storage\Database\PDO\Oracle',
        IDriverSources::TYPE_PDO_POSTGRES => '\kalanis\kw_mapper\Storage\Database\PDO\PostgreSQL',
        IDriverSources::TYPE_PDO_SQLITE => '\kalanis\kw_mapper\Storage\Database\PDO\SQLite',
    ];

    protected static $instances = [];

    public static function getInstance(): self
    {
        return new static();
    }

    /**
     * @param Config $config
     * @return ADatabase
     * @throws MapperException
     */
    public function getDatabase(Config $config): ADatabase
    {
        if (empty(static::$instances[$config->getDriver()])) {
            if (empty(static::$map[$config->getDriver()])) {
                throw new MapperException(sprintf('Wanted source %s not exists!', $config->getDriver()));
            }
            $path = static::$map[$config->getDriver()];
            $instance = new $path($config);
            if (!$instance instanceof ADatabase) {
                throw new MapperException(sprintf('Defined class %s is not instance of Storage\ADatabase!', $path));
            }
            static::$instances[$config->getDriver()] = $instance;
        }
        return static::$instances[$config->getDriver()];
    }
}

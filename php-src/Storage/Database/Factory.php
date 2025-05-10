<?php

namespace kalanis\kw_mapper\Storage\Database;


use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\MapperException;
use ReflectionClass;
use ReflectionException;


/**
 * Class Factory
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 */
class Factory
{
    /** @var array<string, string> */
    protected static array $map = [
        IDriverSources::TYPE_PDO_MYSQL => PDO\MySQL::class,
        IDriverSources::TYPE_PDO_MSSQL => PDO\MSSQL::class,
        IDriverSources::TYPE_PDO_MSSQL_T => PDO\MSSQLTrusting::class,
        IDriverSources::TYPE_PDO_ORACLE => PDO\Oracle::class,
        IDriverSources::TYPE_PDO_POSTGRES => PDO\PostgreSQL::class,
        IDriverSources::TYPE_PDO_SQLITE => PDO\SQLite::class,
        IDriverSources::TYPE_RAW_MYSQLI => Raw\MySQLi::class,
        IDriverSources::TYPE_RAW_ORACLE => Raw\Oracle::class,
        IDriverSources::TYPE_RAW_MONGO => Raw\MongoDb::class,
        IDriverSources::TYPE_RAW_LDAP => Raw\Ldap::class,
        IDriverSources::TYPE_RAW_WINREG => Raw\WinRegistry::class,
        IDriverSources::TYPE_RAW_WINREG2 => Raw\WinRegistry2::class,
        IDriverSources::TYPE_RAW_DBA => Raw\Dba::class,
    ];

    /** @var array<string, ADatabase> */
    protected static array $instances = [];

    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * @param Config $config
     * @throws MapperException
     * @return ADatabase
     */
    public function getDatabase(Config $config): ADatabase
    {
        if (empty(static::$instances[$config->getDriver()])) {
            if (empty(static::$map[$config->getDriver()])) {
                throw new MapperException(sprintf('Wanted source *%s* not exists!', $config->getDriver()));
            }
            $path = static::$map[$config->getDriver()];
            try {
                /** @var class-string $path */
                $reflect = new ReflectionClass($path);
                $instance = $reflect->newInstance($config);
            } catch (ReflectionException $ex) {
                throw new MapperException($ex->getMessage(), $ex->getCode(), $ex);
            }
            if (!$instance instanceof ADatabase) {
                throw new MapperException(sprintf('Defined class *%s* is not instance of Storage\ADatabase!', $path));
            }
            static::$instances[$config->getDriver()] = $instance;
        }
        return static::$instances[$config->getDriver()];
    }
}

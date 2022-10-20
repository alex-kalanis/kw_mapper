<?php

namespace StorageTests\Database;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database;


class FactoryTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testFactoryNoClass(): void
    {
        $conf = Database\Config::init()->setTarget(
            'unknown',
            'test_conf',
            ':--memory--:',
            12345678,
            'foo',
            'bar',
            'baz'
        );
        $factory = new SpecFactory();
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Wanted source *unknown* not exists!');
        $factory->getDatabase($conf);
    }

    /**
     * @throws MapperException
     */
    public function testFactoryBadClass(): void
    {
        $conf = Database\Config::init()->setTarget(
            'failed_one',
            'test_conf',
            ':--memory--:',
            987654,
            'foo',
            'bar',
            'baz'
        );
        $factory = new SpecFactory();
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Defined class *StorageTests\Database\FailedDatabaseClass* is not instance of Storage\ADatabase!');
        $factory->getDatabase($conf);
    }

    /**
     * @throws MapperException
     */
    public function testFactoryRun(): void
    {
        $conf = Database\Config::init()->setTarget(
            IDriverSources::TYPE_PDO_POSTGRES,
            'test_conf',
            ':--memory--:',
            12345678,
            'foo',
            'bar',
            'baz'
        );
        $factory = new SpecFactory();
        $class = $factory->getDatabase($conf);
        $this->assertInstanceOf(Database\PDO\PostgreSQL::class, $class);
    }

    /**
     * @throws MapperException
     */
    public function testConnectSingleton(): void
    {
        $conf = Database\Config::init()->setTarget(
            IDriverSources::TYPE_PDO_MYSQL,
            'another_conf',
            ':--memory--:',
            951357,
            'foo',
            'bar',
            'baz'
        );
        XSingleton::clear();
        $lib = XSingleton::getInstance();
        $obj = $lib->getDatabase($conf);
        $this->assertInstanceOf(Database\ADatabase::class, $obj);
        $obj->addAttribute('fix', 'something');
    }
}


class FailedDatabaseClass
{
    public function __construct(Database\Config $config)
    {
        // intentionally nothing to do and not instance of ADatabase
    }
}


class SpecFactory extends Database\Factory
{
    protected static $map = [
        IDriverSources::TYPE_PDO_POSTGRES => Database\PDO\PostgreSQL::class,
        IDriverSources::TYPE_PDO_SQLITE => Database\PDO\SQLite::class,
        'failed_one' => FailedDatabaseClass::class,
    ];
}


class XSingleton extends Database\DatabaseSingleton
{
    public static function clear(): void
    {
        static::$instance = null;
    }
}

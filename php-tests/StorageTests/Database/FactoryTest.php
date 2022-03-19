<?php

namespace StorageTests\Database;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Config;
use kalanis\kw_mapper\Storage\Database\DatabaseSingleton;
use kalanis\kw_mapper\Storage\Database\Factory;


class FactoryTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testFactoryNoClass(): void
    {
        $conf = Config::init()->setTarget(
            'unknown',
            'test_conf',
            ':--memory--:',
            12345678,
            'foo',
            'bar',
            'baz'
        );
        $factory = SpecFactory::getInstance();
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Wanted source *unknown* not exists!');
        $factory->getDatabase($conf);
    }

    /**
     * @throws MapperException
     */
    public function testFactoryBadClass(): void
    {
        $conf = Config::init()->setTarget(
            'failed_one',
            'test_conf',
            ':--memory--:',
            987654,
            'foo',
            'bar',
            'baz'
        );
        $factory = SpecFactory::getInstance();
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Defined class *\StorageTests\Database\FailedDatabaseClass* is not instance of Storage\ADatabase!');
        $factory->getDatabase($conf);
    }

    /**
     * @throws MapperException
     */
    public function testFactoryRun(): void
    {
        $conf = Config::init()->setTarget(
            IDriverSources::TYPE_PDO_POSTGRES,
            'test_conf',
            ':--memory--:',
            12345678,
            'foo',
            'bar',
            'baz'
        );
        $factory = SpecFactory::getInstance();
        $class = $factory->getDatabase($conf);
        $this->assertInstanceOf('\kalanis\kw_mapper\Storage\Database\PDO\PostgreSQL', $class);
    }

    /**
     * @throws MapperException
     */
    public function testConnectSingleton(): void
    {
        $conf = Config::init()->setTarget(
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
        $this->assertInstanceOf('\kalanis\kw_mapper\Storage\Database\ADatabase', $obj);
        $obj->addAttribute('fix', 'something');
    }
}


class FailedDatabaseClass
{
    public function __construct(Config $config)
    {
        // intentionally nothing to do and not instance of ADatabase
    }
}


class SpecFactory extends Factory
{
    protected static $map = [
        IDriverSources::TYPE_PDO_POSTGRES => '\kalanis\kw_mapper\Storage\Database\PDO\PostgreSQL',
        IDriverSources::TYPE_PDO_SQLITE => '\kalanis\kw_mapper\Storage\Database\PDO\SQLite',
        'failed_one' => '\StorageTests\Database\FailedDatabaseClass',
    ];
}


class XSingleton extends DatabaseSingleton
{
    public static function clear(): void
    {
        static::$instance = null;
    }
}

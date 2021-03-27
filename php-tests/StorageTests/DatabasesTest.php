<?php

namespace StorageTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Config;
use kalanis\kw_mapper\Storage\Database\Factory;


class DatabasesTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testFactoryNoClass()
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
        $factory = Factory::getInstance();
        $this->expectException(MapperException::class);
        $factory->getDatabase($conf);
    }

    /**
     * @throws MapperException
     */
    public function testFactoryRun()
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
        $factory = Factory::getInstance();
        $class = $factory->getDatabase($conf);
        $this->assertInstanceOf('\kalanis\kw_mapper\Storage\Database\PDO\PostgreSQL', $class);
    }

}

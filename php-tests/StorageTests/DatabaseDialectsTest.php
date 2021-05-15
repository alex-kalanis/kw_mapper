<?php

namespace StorageTests;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Dialects;


class DatabaseDialectsTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testFactoryNoClass()
    {
        $factory = Dialects\Factory::getInstance();
        $this->expectException(MapperException::class);
        $factory->getDialectClass('undefined');
    }

    /**
     * @throws MapperException
     */
    public function testFactoryWrongClass()
    {
        $factory = Dialects\Factory::getInstance();
        $this->expectException(MapperException::class);
        $factory->getDialectClass('\kalanis\kw_mapper\Adapters\MappedStdClass');
    }

    /**
     * @throws MapperException
     */
    public function testFactoryRun()
    {
        $factory = Dialects\Factory::getInstance();
        $class = $factory->getDialectClass('\kalanis\kw_mapper\Storage\Database\Dialects\SQLite');
        $this->assertInstanceOf('\kalanis\kw_mapper\Storage\Database\Dialects\ADialect', $class);
    }

}

<?php

namespace StorageTests\Database\Dialects;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Dialects;


class FactoryTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testFactoryNoClass(): void
    {
        $factory = Dialects\Factory::getInstance();
        $this->expectException(MapperException::class);
        $factory->getDialectClass('undefined');
    }

    /**
     * @throws MapperException
     */
    public function testFactoryWrongClass(): void
    {
        $factory = Dialects\Factory::getInstance();
        $this->expectException(MapperException::class);
        $factory->getDialectClass('\kalanis\kw_mapper\Adapters\MappedStdClass');
    }

    /**
     * @throws MapperException
     */
    public function testFactoryRun(): void
    {
        $factory = Dialects\Factory::getInstance();
        $className = '\kalanis\kw_mapper\Storage\Database\Dialects\SQLite';
        $class = $factory->getDialectClass($className);
        $this->assertInstanceOf('\kalanis\kw_mapper\Storage\Database\Dialects\ADialect', $class);
        // multiple times - one instance
        $this->assertEquals($class, $factory->getDialectClass($className));
    }
}

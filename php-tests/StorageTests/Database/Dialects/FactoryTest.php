<?php

namespace StorageTests\Database\Dialects;


use CommonTestClass;
use kalanis\kw_mapper\Adapters;
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
        $factory->getDialectClass(Adapters\MappedStdClass::class);
    }

    /**
     * @throws MapperException
     */
    public function testFactoryRun(): void
    {
        $factory = Dialects\Factory::getInstance();
        $className = Dialects\SQLite::class;
        $class = $factory->getDialectClass($className);
        $this->assertInstanceOf(Dialects\ADialect::class, $class);
        // multiple times - one instance
        $this->assertEquals($class, $factory->getDialectClass($className));
    }
}

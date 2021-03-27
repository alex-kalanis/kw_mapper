<?php

namespace StorageTests;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Dialects;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


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


class Dialect extends Dialects\ADialect
{

    public function insert(QueryBuilder $builder)
    {
        return '';
    }

    public function select(QueryBuilder $builder)
    {
        return '';
    }

    public function update(QueryBuilder $builder)
    {
        return '';
    }

    public function delete(QueryBuilder $builder)
    {
        return '';
    }

    public function describe(QueryBuilder $builder)
    {
        return '';
    }

    public function availableJoins(): array
    {
        return [];
    }
}


class EscapedDialect extends Dialects\AEscapedDialect
{

    public function insert(QueryBuilder $builder)
    {
        return '';
    }

    public function select(QueryBuilder $builder)
    {
        return '';
    }

    public function update(QueryBuilder $builder)
    {
        return '';
    }

    public function delete(QueryBuilder $builder)
    {
        return '';
    }

    public function describe(QueryBuilder $builder)
    {
        return '';
    }

    public function availableJoins(): array
    {
        return [];
    }
}

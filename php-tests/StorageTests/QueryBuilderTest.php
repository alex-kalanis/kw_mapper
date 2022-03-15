<?php

namespace StorageTests;


use Builder;
use Builder2;
use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Dialects\EmptyDialect;
use kalanis\kw_mapper\Storage\Database\Dialects\MySQL;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


class QueryBuilderTest extends CommonTestClass
{
    public function testBuilderColumn()
    {
        $data = new QueryBuilder\Column();
        $data->setData('foo', 'bar', 'baz', 'anf');
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals('baz', $data->getColumnAlias());
        $this->assertEquals('anf', $data->getAggregate());
    }

    public function testBuilderCondition()
    {
        $data = new QueryBuilder\Condition();
        $data->setData('foo', 'bar', 'baz', 'anf');
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals('baz', $data->getOperation());
        $this->assertEquals('anf', $data->getColumnKey());
    }

    public function testBuilderGroup()
    {
        $data = new QueryBuilder\Group();
        $data->setData('foo', 'bar');
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
    }

    public function testBuilderJoin()
    {
        $data = new QueryBuilder\Join();
        $data->setData('foo', 'bar', 'baz', 'anf', 'bvt', 'xcu', 'xdh');
        $this->assertEquals('foo', $data->getJoinUnderAlias());
        $this->assertEquals('bar', $data->getNewTableName());
        $this->assertEquals('baz', $data->getNewColumnName());
        $this->assertEquals('anf', $data->getKnownTableName());
        $this->assertEquals('bvt', $data->getKnownColumnName());
        $this->assertEquals('xcu', $data->getSide());
        $this->assertEquals('xdh', $data->getTableAlias());
    }

    public function testBuilderOrder()
    {
        $data = new QueryBuilder\Order();
        $data->setData('foo', 'bar', 'baz');
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals('baz', $data->getDirection());
    }

    public function testBuilderProperty()
    {
        $data = new QueryBuilder\Property();
        $data->setData('foo', 'bar', 'baz');
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals('baz', $data->getColumnKey());
    }

    /**
     * @throws MapperException
     */
    public function testColumnFail()
    {
        $builder = new Builder();
        $this->expectException(MapperException::class);
        $builder->addColumn('foo', 'bar', 'baz', 'anf');
    }

    /**
     * @throws MapperException
     */
    public function testColumnPass()
    {
        $builder = new Builder();
        $builder->addColumn('foo', 'bar', 'baz');
        $data = $builder->getColumns();
        $data = reset($data);
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals('baz', $data->getColumnAlias());
    }

    /**
     * @throws MapperException
     */
    public function testConditionFail()
    {
        $builder = new Builder();
        $this->expectException(MapperException::class);
        $builder->addCondition('foo', 'bar', 'baz', 'anf');
    }

    /**
     * @throws MapperException
     */
    public function testConditionPass()
    {
        $builder = new Builder();
        $builder->addCondition('foo', 'bar', IQueryBuilder::OPERATION_EQ, 'anf');
        $data = $builder->getConditions();
        $data = reset($data);
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals(IQueryBuilder::OPERATION_EQ, $data->getOperation());
        $this->assertEquals('anf', $builder->getParams()[$data->getColumnKey()]);
        $builder->resetCounter();
    }

    public function testProperty()
    {
        $builder = new Builder();
        $builder->addProperty('foo', 'bar', 'baz');
        $data = $builder->getProperties();
        $data = reset($data);
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals('baz', $builder->getParams()[$data->getColumnKey()]);
        $builder->resetCounter();
    }

    public function testJoin()
    {
        $builder = new Builder();
        $builder->addJoin('foo', 'bar', 'baz', 'anf', 'bvt', 'xcu', 'xdh');
        $data = $builder->getJoins();
        $data = reset($data);
        $this->assertEquals('foo', $data->getJoinUnderAlias());
        $this->assertEquals('bar', $data->getNewTableName());
        $this->assertEquals('baz', $data->getNewColumnName());
        $this->assertEquals('anf', $data->getKnownTableName());
        $this->assertEquals('bvt', $data->getKnownColumnName());
        $this->assertEquals('xcu', $data->getSide());
        $this->assertEquals('xdh', $data->getTableAlias());
    }

    /**
     * @throws MapperException
     */
    public function testOrderFail()
    {
        $builder = new Builder();
        $this->expectException(MapperException::class);
        $builder->addOrderBy('foo', 'bar', 'baz');
    }

    /**
     * @throws MapperException
     */
    public function testOrderPass()
    {
        $builder = new Builder();
        $builder->addOrderBy('foo', 'bar', IQueryBuilder::ORDER_DESC);
        $data = $builder->getOrdering();
        $data = reset($data);
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals(IQueryBuilder::ORDER_DESC, $data->getDirection());
    }

    public function testGroup()
    {
        $builder = new Builder();
        $builder->addGroupBy('foo', 'bar');
        $data = $builder->getGrouping();
        $data = reset($data);
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
    }

    public function testLimits()
    {
        $builder = new Builder();
        $builder->setLimits(75, 12);
        $this->assertEquals(75, $builder->getOffset());
        $this->assertEquals(12, $builder->getLimit());
        $builder->setLimit(null);
        $this->assertEmpty($builder->getLimit());
        $builder->setOffset(null);
        $this->assertEmpty($builder->getOffset());
    }

    public function testBasics()
    {
        $builder = new Builder();
        $builder->setBaseTable('foo');
        $this->assertEquals('foo', $builder->getBaseTable());
        $builder->setRelations(IQueryBuilder::RELATION_OR);
        $this->assertEquals(IQueryBuilder::RELATION_OR, $builder->getRelation());
        $builder->setRelations('dfg');
        $this->assertEquals(IQueryBuilder::RELATION_OR, $builder->getRelation());
        $builder->clearColumns();
        $builder->clear();
    }

    public function testJoins()
    {
        $builder = new Builder2(new MySQL());
        $builder->addJoin('foo', 'bar', 'baz', 'anf', 'bvt', 'CROSS', 'xdh');
        $data = $builder->getJoins();
        $data = reset($data);
        $this->assertEquals('foo', $data->getJoinUnderAlias());
        $this->assertEquals('bar', $data->getNewTableName());
        $this->assertEquals('baz', $data->getNewColumnName());
        $this->assertEquals('anf', $data->getKnownTableName());
        $this->assertEquals('bvt', $data->getKnownColumnName());
        $this->assertEquals('CROSS', $data->getSide());
        $this->assertEquals('xdh', $data->getTableAlias());
    }

    /**
     * @throws MapperException
     */
    public function testJoinsFail()
    {
        $builder = new Builder2(new EmptyDialect());
        $this->expectException(MapperException::class);
        $builder->addJoin('foo', 'bar', 'baz', 'anf', 'bvt', 'xcu', 'xdh');
    }
}

<?php

namespace StorageTests\Database;


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
    public function testBuilderColumn(): void
    {
        $data = new QueryBuilder\Column();
        $data->setData('foo', 'bar', 'baz', 'anf');
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals('baz', $data->getColumnAlias());
        $this->assertEquals('anf', $data->getAggregate());
    }

    public function testBuilderCondition(): void
    {
        $data = new QueryBuilder\Condition();
        $data->setData('foo', 'bar', 'baz', 'anf');
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals('baz', $data->getOperation());
        $this->assertEquals('anf', $data->getColumnKey());
        $this->assertFalse($data->isRaw());
        $this->assertEquals('', $data->getRaw());
    }

    public function testBuilderRaw(): void
    {
        $data = new QueryBuilder\Condition();
        $data->setRaw('foo bar baz anf', 'uhbgzv');
        $this->assertEquals('', $data->getTableName());
        $this->assertEquals('', $data->getColumnName());
        $this->assertEquals('', $data->getOperation());
        $this->assertEquals('uhbgzv', $data->getColumnKey());
        $this->assertTrue($data->isRaw());
        $this->assertEquals('foo bar baz anf', $data->getRaw());
    }

    public function testBuilderGroup(): void
    {
        $data = new QueryBuilder\Group();
        $data->setData('foo', 'bar');
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
    }

    public function testBuilderJoin(): void
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

    public function testBuilderOrder(): void
    {
        $data = new QueryBuilder\Order();
        $data->setData('foo', 'bar', 'baz');
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals('baz', $data->getDirection());
    }

    public function testBuilderProperty(): void
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
    public function testColumnFail(): void
    {
        $builder = new Builder();
        $this->expectException(MapperException::class);
        $builder->addColumn('foo', 'bar', 'baz', 'anf');
    }

    /**
     * @throws MapperException
     */
    public function testColumnPass(): void
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
    public function testConditionFail(): void
    {
        $builder = new Builder();
        $this->expectException(MapperException::class);
        $builder->addCondition('foo', 'bar', 'baz', 'anf');
    }

    /**
     * @throws MapperException
     */
    public function testConditionPass(): void
    {
        $builder = new Builder();
        $builder->addCondition('foo', 'bar', IQueryBuilder::OPERATION_EQ, 'anf');
        $data = $builder->getConditions();
        $data = reset($data);
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals(IQueryBuilder::OPERATION_EQ, $data->getOperation());
        $this->assertEquals('anf', $builder->getParams()[$data->getColumnKey()]);
        $this->assertEquals('', $data->getRaw());
        $builder->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testRawConditionPass1(): void
    {
        $builder = new Builder();
        $builder->addRawCondition('bar baz');
        $data = $builder->getConditions();
        $data = reset($data);
        $this->assertEquals('', $data->getTableName());
        $this->assertEquals('', $data->getColumnName());
        $this->assertEquals('', $data->getOperation());
        $this->assertEquals('', $data->getColumnKey());
        $this->assertFalse(isset($builder->getParams()[$data->getColumnKey()]));
        $this->assertEquals('bar baz', $data->getRaw());
        $builder->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testRawConditionPass2(): void
    {
        $builder = new Builder();
        $builder->addRawCondition('bar baz', 'foo', 'hsf');
        $data = $builder->getConditions();
        $data = reset($data);
        $this->assertEquals('', $data->getTableName());
        $this->assertEquals('', $data->getColumnName());
        $this->assertEquals(':foo_0', $data->getColumnKey());
        $this->assertEquals('', $data->getOperation());
        $this->assertEquals('hsf', $builder->getParams()[$data->getColumnKey()]);
        $this->assertEquals('bar baz', $data->getRaw());
        $builder->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testConditionNullPass1(): void
    {
        $builder = new Builder();
        $builder->addCondition('foo', 'bar', IQueryBuilder::OPERATION_EQ, null);
        $data = $builder->getConditions();
        $data = reset($data);
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals(IQueryBuilder::OPERATION_NULL, $data->getOperation());
        $this->assertFalse(isset($builder->getParams()[$data->getColumnKey()]));
        $builder->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testConditionNullPass2(): void
    {
        $builder = new Builder();
        $builder->addCondition('foo', 'bar', IQueryBuilder::OPERATION_NEQ, null);
        $data = $builder->getConditions();
        $data = reset($data);
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals(IQueryBuilder::OPERATION_NNULL, $data->getOperation());
        $this->assertFalse(isset($builder->getParams()[$data->getColumnKey()]));
        $builder->resetCounter();
    }

    public function testProperty(): void
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

    public function testJoin(): void
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
    public function testOrderFail(): void
    {
        $builder = new Builder();
        $this->expectException(MapperException::class);
        $builder->addOrderBy('foo', 'bar', 'baz');
    }

    /**
     * @throws MapperException
     */
    public function testOrderPass(): void
    {
        $builder = new Builder();
        $builder->addOrderBy('foo', 'bar', IQueryBuilder::ORDER_DESC);
        $data = $builder->getOrdering();
        $data = reset($data);
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals(IQueryBuilder::ORDER_DESC, $data->getDirection());
    }

    public function testGroup(): void
    {
        $builder = new Builder();
        $builder->addGroupBy('foo', 'bar');
        $data = $builder->getGrouping();
        $data = reset($data);
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
    }

    /**
     * @throws MapperException
     */
    public function testHavingFail(): void
    {
        $builder = new Builder();
        $this->expectException(MapperException::class);
        $builder->addHavingCondition('foo', 'bar', 'baz', 'anf');
    }

    /**
     * @throws MapperException
     */
    public function testHavingPass(): void
    {
        $builder = new Builder();
        $builder->addHavingCondition('foo', 'bar', IQueryBuilder::OPERATION_EQ, 'anf');
        $data = $builder->getHavingCondition();
        $data = reset($data);
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals(IQueryBuilder::OPERATION_EQ, $data->getOperation());
        $this->assertEquals('anf', $builder->getParams()[$data->getColumnKey()]);
        $builder->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testHavingNullsPass1(): void
    {
        $builder = new Builder();
        $builder->addHavingCondition('foo', 'bar', IQueryBuilder::OPERATION_EQ, null);
        $data = $builder->getHavingCondition();
        $data = reset($data);
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals(IQueryBuilder::OPERATION_NULL, $data->getOperation());
        $this->assertFalse(isset($builder->getParams()[$data->getColumnKey()]));
        $builder->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testHavingNullsPass2(): void
    {
        $builder = new Builder();
        $builder->addHavingCondition('foo', 'bar', IQueryBuilder::OPERATION_NEQ, null);
        $data = $builder->getHavingCondition();
        $data = reset($data);
        $this->assertEquals('foo', $data->getTableName());
        $this->assertEquals('bar', $data->getColumnName());
        $this->assertEquals(IQueryBuilder::OPERATION_NNULL, $data->getOperation());
        $this->assertFalse(isset($builder->getParams()[$data->getColumnKey()]));
        $builder->resetCounter();
    }

    public function testLimits(): void
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

    public function testBasics(): void
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

    public function testJoins(): void
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
    public function testJoinsFail(): void
    {
        $builder = new Builder2(new EmptyDialect());
        $this->expectException(MapperException::class);
        $builder->addJoin('foo', 'bar', 'baz', 'anf', 'bvt', 'xcu', 'xdh');
    }
}

<?php

namespace StorageTests;


use Builder;
use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Dialects;


class DatabaseDialectPostgresTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testDescribe()
    {
        $query = new Builder();
        $query->setBaseTable('foo');
        $sql = new Dialects\PostgreSQL();
        $this->assertEquals('SELECT table_name, column_name, data_type FROM information_schema.columns WHERE table_name = \'foo\';', $sql->describe($query));
        $this->assertNotEmpty($sql->availableJoins());
        $query->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testInsert()
    {
        $query = new Builder();
        $query->setBaseTable('foo');
        $query->addProperty('foo', 'bar', 'baz');
        $query->addProperty('foo', 'htf', 'yjd');
        $query->addProperty('foo', 'vrs', 'abh');
        $sql = new Dialects\PostgreSQL();
        $this->assertEquals('INSERT INTO foo (bar, htf, vrs) VALUES (:bar_0, :htf_1, :vrs_2);', $sql->insert($query));
        $this->assertEquals([ ':bar_0' => 'baz', ':htf_1' => 'yjd', ':vrs_2' => 'abh', ], $query->getParams());
        $query->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testDelete()
    {
        $query = new Builder();
        $query->setBaseTable('foo');
        $query->addCondition('foo', 'dbt', IQueryBuilder::OPERATION_EQ, 'ggf');
        $query->addCondition('foo', 'dfd', IQueryBuilder::OPERATION_NEQ, 'yxn');
        $query->setLimit(5);
        $sql = new Dialects\PostgreSQL();
        $this->assertEquals('DELETE FROM foo WHERE foo.dbt = :dbt_0 AND foo.dfd != :dfd_1;', $sql->delete($query));
        $this->assertEquals([ ':dbt_0' => 'ggf', ':dfd_1' => 'yxn', ], $query->getParams());
        $query->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testUpdate()
    {
        $query = new Builder();
        $query->setBaseTable('foo');
        $query->addProperty('foo', 'bar', 'baz');
        $query->addProperty('foo', 'htf', 'yjd');
        $query->addProperty('foo', 'vrs', 'abh');
        $query->addCondition('foo', 'dbt', IQueryBuilder::OPERATION_EQ, 'ggf');
        $query->addCondition('foo', 'dfd', IQueryBuilder::OPERATION_NEQ, 'yxn');
        $sql = new Dialects\PostgreSQL();
        $this->assertEquals('UPDATE foo SET bar = :bar_0, htf = :htf_1, vrs = :vrs_2 WHERE foo.dbt = :dbt_3 AND foo.dfd != :dfd_4;', $sql->update($query));
        $this->assertEquals([ ':bar_0' => 'baz', ':htf_1' => 'yjd', ':vrs_2' => 'abh', ':dbt_3' => 'ggf', ':dfd_4' => 'yxn', ], $query->getParams());
        $query->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testSelect()
    {
        $query = new Builder();
        $query->setBaseTable('foo');
        $query->addColumn('foo', 'bar', 'baz');
        $query->addColumn('foo', 'htf', 'yjd');
        $query->addColumn('dfg', 'vrs', 'abh');
        $query->addJoin('dfg', 'btr', 'gda', 'foo', 'fds', IQueryBuilder::JOIN_LEFT, 'dfg');
        $query->addCondition('foo', 'dbt', IQueryBuilder::OPERATION_EQ, 'ggf');
        $query->addCondition('foo', 'dfd', IQueryBuilder::OPERATION_NEQ, 'yxn');
        $query->addOrderBy('dfg', 'vrs', IQueryBuilder::ORDER_ASC);
        $query->addGroupBy('foo', 'gds');
        $query->setLimits(5,3);
        $sql = new Dialects\PostgreSQL();
        $this->assertEquals('SELECT foo.bar AS baz, foo.htf AS yjd, dfg.vrs AS abh FROM foo  LEFT JOIN btr AS dfg ON (foo.fds = dfg.gda)  WHERE foo.dbt = :dbt_0 AND foo.dfd != :dfd_1 GROUP BY foo.gds ORDER BY dfg.vrs ASC LIMIT 3 OFFSET 5;', $sql->select($query));
        $query->setOffset(null);
        $this->assertEquals('SELECT foo.bar AS baz, foo.htf AS yjd, dfg.vrs AS abh FROM foo  LEFT JOIN btr AS dfg ON (foo.fds = dfg.gda)  WHERE foo.dbt = :dbt_0 AND foo.dfd != :dfd_1 GROUP BY foo.gds ORDER BY dfg.vrs ASC LIMIT 3;', $sql->select($query));
        $query->setLimit(null);
        $this->assertEquals('SELECT foo.bar AS baz, foo.htf AS yjd, dfg.vrs AS abh FROM foo  LEFT JOIN btr AS dfg ON (foo.fds = dfg.gda)  WHERE foo.dbt = :dbt_0 AND foo.dfd != :dfd_1 GROUP BY foo.gds ORDER BY dfg.vrs ASC;', $sql->select($query));
        $this->assertEquals([ ':dbt_0' => 'ggf', ':dfd_1' => 'yxn', ], $query->getParams());
        $query->resetCounter();
    }
}

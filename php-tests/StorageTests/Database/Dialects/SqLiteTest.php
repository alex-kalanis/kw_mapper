<?php

namespace StorageTests\Database\Dialects;


use Builder;
use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Dialects;


class SqLiteTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testDescribe(): void
    {
        $query = new Builder();
        $query->setBaseTable('foo');
        $sql = new Dialects\SQLite();
        $this->assertEquals('SELECT `sql` FROM `sqlite_master` WHERE `name` = \'foo\';', $sql->describe($query));
        $this->assertNotEmpty($sql->availableJoins());
        $query->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testInsert(): void
    {
        $query = new Builder();
        $query->setBaseTable('foo');
        $query->addProperty('foo', 'bar', 'baz');
        $query->addProperty('foo', 'htf', 'yjd');
        $query->addProperty('foo', 'vrs', 'abh');
        $sql = new Dialects\SQLite();
        $this->assertEquals('INSERT INTO `foo` SET `bar` = :bar_0, `htf` = :htf_1, `vrs` = :vrs_2;', $sql->insert($query));
        $this->assertEquals([ ':bar_0' => 'baz', ':htf_1' => 'yjd', ':vrs_2' => 'abh', ], $query->getParams());
        $query->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testDelete(): void
    {
        $query = new Builder();
        $query->setBaseTable('foo');
        $query->addCondition('foo', 'dbt', IQueryBuilder::OPERATION_EQ, 'ggf');
        $query->addCondition('foo', 'dfd', IQueryBuilder::OPERATION_NEQ, 'yxn');
        $query->setLimit(5);
        $sql = new Dialects\SQLite();
        $this->assertEquals('DELETE FROM `foo` WHERE `foo`.`dbt` = :dbt_0 AND `foo`.`dfd` != :dfd_1;', $sql->delete($query));
        $this->assertEquals([ ':dbt_0' => 'ggf', ':dfd_1' => 'yxn', ], $query->getParams());
        $query->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testUpdate(): void
    {
        $query = new Builder();
        $query->setBaseTable('foo');
        $query->addProperty('foo', 'bar', 'baz');
        $query->addProperty('foo', 'htf', 'yjd');
        $query->addProperty('foo', 'vrs', 'abh');
        $query->addCondition('foo', 'dbt', IQueryBuilder::OPERATION_EQ, 'ggf');
        $query->addCondition('foo', 'dfd', IQueryBuilder::OPERATION_NEQ, 'yxn');
        $sql = new Dialects\SQLite();
        $this->assertEquals('UPDATE `foo` SET `bar` = :bar_0, `htf` = :htf_1, `vrs` = :vrs_2 WHERE `foo`.`dbt` = :dbt_3 AND `foo`.`dfd` != :dfd_4;', $sql->update($query));
        $this->assertEquals([ ':bar_0' => 'baz', ':htf_1' => 'yjd', ':vrs_2' => 'abh', ':dbt_3' => 'ggf', ':dfd_4' => 'yxn', ], $query->getParams());
        $query->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testSelect(): void
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
        $sql = new Dialects\SQLite();
        $this->assertEquals('SELECT `foo`.`bar` AS `baz`, `foo`.`htf` AS `yjd`, `dfg`.`vrs` AS `abh` FROM `foo`  LEFT JOIN `btr` AS `dfg` ON (`foo`.`fds` = `dfg`.`gda`)  WHERE `foo`.`dbt` = :dbt_0 AND `foo`.`dfd` != :dfd_1 GROUP BY `foo`.`gds` ORDER BY `dfg`.`vrs` ASC LIMIT 3 OFFSET 5;', $sql->select($query));
        $query->setOffset(null);
        $this->assertEquals('SELECT `foo`.`bar` AS `baz`, `foo`.`htf` AS `yjd`, `dfg`.`vrs` AS `abh` FROM `foo`  LEFT JOIN `btr` AS `dfg` ON (`foo`.`fds` = `dfg`.`gda`)  WHERE `foo`.`dbt` = :dbt_0 AND `foo`.`dfd` != :dfd_1 GROUP BY `foo`.`gds` ORDER BY `dfg`.`vrs` ASC LIMIT 3;', $sql->select($query));
        $query->setLimit(null);
        $this->assertEquals('SELECT `foo`.`bar` AS `baz`, `foo`.`htf` AS `yjd`, `dfg`.`vrs` AS `abh` FROM `foo`  LEFT JOIN `btr` AS `dfg` ON (`foo`.`fds` = `dfg`.`gda`)  WHERE `foo`.`dbt` = :dbt_0 AND `foo`.`dfd` != :dfd_1 GROUP BY `foo`.`gds` ORDER BY `dfg`.`vrs` ASC;', $sql->select($query));
        $this->assertEquals([ ':dbt_0' => 'ggf', ':dfd_1' => 'yxn', ], $query->getParams());
        $query->resetCounter();
    }
}

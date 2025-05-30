<?php

namespace SearchTests;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Search\Connector\Records;
use CommonTestClass;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder\Condition;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder\Order;


/**
 * Class ConnectRecordsTest
 * @package SearchTests
 * @requires extension PDO
 * @requires extension pdo_sqlite
 */
class ConnectRecordsTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testSort(): void
    {
        $record1 = new \XSimpleRecord();
        $record1->useMock();
        $record1->title = 'abc';
        $record1->desc = null;

        $record2 = new \XSimpleRecord();
        $record2->useMock();
        $record2->title = 'def';
        $record2->desc = null;

        $sort1 = new Order();
        $sort1->setData('none', 'title', IQueryBuilder::ORDER_ASC);

        $lib = new XSortRecords($record1);
        $records = $lib->xSortResults([$record2, $record1], [$sort1]);
        $result = array_map('iterator_to_array', $records);
        $this->assertEquals(['id' => false, 'title' => 'abc', 'desc' => null], $result[0]);
        $this->assertEquals(['id' => false, 'title' => 'def', 'desc' => null], $result[1]);

        $sort2 = new Order();
        $sort2->setData('none', 'title', IQueryBuilder::ORDER_DESC);

        $lib = new XSortRecords($record1);
        $records = $lib->xSortResults([$record2, $record1], [$sort2]);
        $result = array_map('iterator_to_array', $records);
        $this->assertEquals(['id' => false, 'title' => 'def', 'desc' => null], $result[0]);
        $this->assertEquals(['id' => false, 'title' => 'abc', 'desc' => null], $result[1]);
    }

    /**
     * @throws MapperException
     */
    public function testSort2(): void
    {
        $record1 = new \XSimpleRecord();
        $record1->useMock();
        $record1->title = 'abc';
        $record1->desc = null;

        $record2 = new \XSimpleRecord();
        $record2->useMock();
        $record2->title = 'def';
        $record2->desc = null;

        $condition1 = new Condition();
        $condition1->setRaw([$this, 'checkRecordsByCallback']);

        $lib = new XSortRecords($record1);
        $lib->xSetCondition($condition1);
        $this->assertTrue($lib->filterCondition($record1));
        $this->assertFalse($lib->filterCondition($record2));
    }

    public function checkRecordsByCallback(\XSimpleRecord $record): bool
    {
        return 'abc' == $record->title;
    }

    /**
     * @throws MapperException
     */
    public function testSortFail(): void
    {
        $record1 = new \XSimpleRecord();
        $record1->useMock();
        $record1->title = 'abc';
        $record1->desc = null;

        $record2 = new \XSimpleRecord();
        $record2->useMock();
        $record2->title = 'def';
        $record2->desc = null;

        $lib = new XSortRecords($record1);
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('You must set how it will be sorted!');
        $lib->sortOrder($record1, $record2);
    }

    /**
     * @throws MapperException
     */
    public function testConditionFail(): void
    {
        $record1 = new \XSimpleRecord();
        $record1->useMock();
        $record1->title = 'abc';
        $record1->desc = null;

        $lib = new XSortRecords($record1);
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('You must set conditions first!');
        $lib->filterCondition($record1);
    }
}


class XSortRecords extends Records
{
    public function xSortResults(array $records, array $ordering): array
    {
        return $this->sortResults($records, $ordering);
    }

    public function xSetCondition(?Condition $condition): void
    {
        $this->condition = $condition;
    }
}

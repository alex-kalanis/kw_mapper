<?php

namespace SearchTests;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Search\Connector\Records;
use CommonTestClass;
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

        $record2 = new \XSimpleRecord();
        $record2->useMock();
        $record2->title = 'def';

        $sort1 = new Order();
        $sort1->setData('none', 'title', IQueryBuilder::ORDER_ASC);

        $lib = new XSortRecords($record1);
        $records = $lib->xSortResults([$record2, $record1], [$sort1]);
        $result = array_map('iterator_to_array', $records);
        $this->assertEquals(['id' => false, 'title' => 'abc'], $result[0]);
        $this->assertEquals(['id' => false, 'title' => 'def'], $result[1]);

        $sort2 = new Order();
        $sort2->setData('none', 'title', IQueryBuilder::ORDER_DESC);

        $lib = new XSortRecords($record1);
        $records = $lib->xSortResults([$record2, $record1], [$sort2]);
        $result = array_map('iterator_to_array', $records);
        $this->assertEquals(['id' => false, 'title' => 'def'], $result[0]);
        $this->assertEquals(['id' => false, 'title' => 'abc'], $result[1]);
    }

    /**
     * @throws MapperException
     */
    public function testSortFail(): void
    {
        $record1 = new \XSimpleRecord();
        $record1->useMock();
        $record1->title = 'abc';

        $record2 = new \XSimpleRecord();
        $record2->useMock();
        $record2->title = 'def';

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
}

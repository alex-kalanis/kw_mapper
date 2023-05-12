<?php

namespace SearchTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Search\Connector\Records;
use kalanis\kw_mapper\Search\Search;
use kalanis\kw_mapper\Storage;


class FileTableTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testSimpleSearch(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->orderBy('title');
        $this->assertEquals(5, $lib->getCount());
        $lib->offset(1);
        $lib->limit(3);
        $lib->useAnd(); // just call
        $lib->useOr(); // just call
        $this->assertEquals(5, $lib->getCount());
        $this->assertEquals(3, count($lib->getResults()));
    }

    /**
     * @throws MapperException
     */
    public function testSearchFailPropertyDefine(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();

        $lib = new Search($record);
        $this->expectException(MapperException::class);
        $lib->like('.file', 'mm');
    }

    /**
     * @throws MapperException
     */
    public function testSearchLike(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();

        $lib = new Search($record);
        $lib->like('file', 'dummy');
        $this->assertEquals(4, $lib->getCount());
        $this->assertNotEmpty($lib->getResults());

        $lib = new Search($record);
        $lib->like('file.file', 'mm');
        $this->assertEquals(4, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchNotLike(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();

        $lib = new Search($record);
        $lib->notLike('file.file', 'now');
        $this->assertEquals(4, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchExact(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->exact('enabled', true);
        $this->assertEquals(3, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchNotExact(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->notExact('enabled', true);
        $this->assertEquals(2, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchFrom(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->from('id', 2, true);
        $this->assertEquals(4, $lib->getCount());

        $lib = new Search($record);
        $lib->from('id', 2, false);
        $this->assertEquals(3, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchTo(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->to('id', 3, false);
        $this->assertEquals(2, $lib->getCount());

        $lib = new Search($record);
        $lib->to('id', 3, true);
        $this->assertEquals(3, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testRegex(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->regexp('file', '#dummy(\d+)#');
        $this->assertEquals(4, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchBetween(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->between('id', 2, 5);
        $this->assertEquals(4, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchNull(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->null('title');
        $this->assertEquals(0, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchNotNull(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->notNull('title');
        $this->assertEquals(5, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchIn(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->in('desc', ['jkl', 'pqr', 'z12']);
        $this->assertEquals(2, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchNotIn(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->notIn('desc', ['jkl', 'pqr', 'z12']);
        $this->assertEquals(3, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchGrouping(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->groupBy('enabled');
        $this->assertEquals(2, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchChild(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $this->expectException(MapperException::class);
        $lib->child('any');
    }

    /**
     * @throws MapperException
     */
    public function testSearchUnknownChild(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $this->expectException(MapperException::class);
        $lib->childNotExist('any', 'where');
    }

    /**
     * @throws MapperException
     */
    public function testSearchInitial(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Records($record);
        $this->assertEquals(0, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchChildRecord(): void
    {
        $record = new \TableIdRecord();
        $record->useNoKeyMapper();
        $lib = new Records($record);
        $this->expectException(MapperException::class);
        $lib->child('any');
    }

    /**
     * @throws MapperException
     */
    public function testSearchUnknownChildRecord(): void
    {
        $record = new \TableIdRecord();
        $record->useNoKeyMapper();
        $lib = new Records($record);
        $this->expectException(MapperException::class);
        $lib->childNotExist('any', 'where', 'for');
    }

    /**
     * @throws MapperException
     */
    public function testSearchShittyConditionRecord(): void
    {
        $record1 = new \XSimpleRecord();
        $record1->useMock();
        $record1->id = 1;
        $record1->title = 'abc';

        $record2 = new \XSimpleRecord();
        $record2->useMock();
        $record2->id = 2;
        $record2->title = 'def';

        $record3 = new \XSimpleRecord();
        $record3->useMock();
        $record3->id = 3;
        $record3->title = 'ghi';

        $record4 = new \XSimpleRecord();
        $record4->useMock();
        $record4->id = 4;
        $record4->title = 'jkl';

        $lib = new XRecords($record1);
        $lib->setInitialRecords([$record1, $record2, $record3, $record4]);
        $this->expectException(MapperException::class);
        $lib->checkConditionExt('gh....', ':file__', 'something');
    }

    /**
     * @param string $operation
     * @param mixed $value
     * @param mixed $expected
     * @throws MapperException
     * @dataProvider conditionProviderPass
     */
    public function testConditionDataPass(string $operation, $value, $expected): void
    {
        $record = new \XSimpleRecord();
        $record->useMock();
        $lib = new XRecords($record);
        $this->assertTrue($lib->checkConditionExt($operation, $value, $expected));
    }

    public function conditionProviderPass(): array
    {
        return [
            [IQueryBuilder::OPERATION_NULL, null, 'abc'],
            [IQueryBuilder::OPERATION_NNULL, 'abc', 'abc'],
            [IQueryBuilder::OPERATION_EQ, 'abc', 'abc'],
            [IQueryBuilder::OPERATION_EQ, 456, 456],
            [IQueryBuilder::OPERATION_NEQ, 'abc', 'def'],
            [IQueryBuilder::OPERATION_NEQ, 789, 123],
            [IQueryBuilder::OPERATION_GT, 456, 123],
            [IQueryBuilder::OPERATION_GTE, 456, 123],
            [IQueryBuilder::OPERATION_GTE, 456, 456],
            [IQueryBuilder::OPERATION_LT, 456, 789],
            [IQueryBuilder::OPERATION_LTE, 456, 789],
            [IQueryBuilder::OPERATION_LTE, 789, 789],
            [IQueryBuilder::OPERATION_LIKE, 'abcdefghijkl', 'ghij'],
            [IQueryBuilder::OPERATION_NLIKE, 'abcdefghijkl', 'ree'],
            [IQueryBuilder::OPERATION_IN, 'okm', ['ijn', 'uhb', 'zgv', 'okm', 'tfc']],
            [IQueryBuilder::OPERATION_NIN, 'ujm', ['ijn', 'uhb', 'zgv', 'okm', 'tfc']],
        ];
    }

    /**
     * @param string $operation
     * @param mixed $data
     * @throws MapperException
     * @dataProvider conditionProviderFail
     */
    public function testConditionDataFail(string $operation, $value, $expected): void
    {
        $record = new \XSimpleRecord();
        $record->useMock();
        $lib = new XRecords($record);
        $this->assertFalse($lib->checkConditionExt($operation, $value, $expected));
    }

    public function conditionProviderFail(): array
    {
        return [
            [IQueryBuilder::OPERATION_NULL, 'gfbgd', 516],
            [IQueryBuilder::OPERATION_NNULL, null, 'abc'],
            [IQueryBuilder::OPERATION_EQ, 'abc', 'def'],
            [IQueryBuilder::OPERATION_EQ, 456, 951],
            [IQueryBuilder::OPERATION_NEQ, 'abc', 'abc'],
            [IQueryBuilder::OPERATION_NEQ, 357, 357],
            [IQueryBuilder::OPERATION_GT, 456, 789],
            [IQueryBuilder::OPERATION_GT, 456, 456],
            [IQueryBuilder::OPERATION_GTE, 456, 789],
            [IQueryBuilder::OPERATION_LT, 456, 123],
            [IQueryBuilder::OPERATION_LT, 456, 456],
            [IQueryBuilder::OPERATION_LTE, 789, 123],
            [IQueryBuilder::OPERATION_LIKE, 'abcdefghijkl', 'ree'],
            [IQueryBuilder::OPERATION_NLIKE, 'abcdefghijkl', 'ghij'],
            [IQueryBuilder::OPERATION_IN, 'ujm', ['ijn', 'uhb', 'zgv', 'okm', 'tfc']],
            [IQueryBuilder::OPERATION_NIN, 'okm', ['ijn', 'uhb', 'zgv', 'okm', 'tfc']],
        ];
    }

    /**
     * @param string $operation
     * @throws MapperException
     * @dataProvider conditionProviderDie
     */
    public function testConditionDataDie(string $operation): void
    {
        $record = new \XSimpleRecord();
        $record->useMock();
        $lib = new XRecords($record);
        $this->expectException(MapperException::class);
        $lib->checkConditionExt($operation, ':file__', 'not_something_expected');
    }

    public function conditionProviderDie(): array
    {
        return [
            ['wat'],
            ['123456789'],
        ];
    }
}


class XRecords extends Records
{
    /**
     * @param string $operation
     * @param mixed $value
     * @param mixed $expected
     * @throws MapperException
     * @return bool
     */
    public function checkConditionExt(string $operation, $value, $expected): bool
    {
        return $this->checkCondition($operation, $value, $expected);
    }
}

<?php

namespace StorageTests\Database;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Dialects;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


class DialectTest extends CommonTestClass
{
    /**
     * @param string $operation
     * @param string $expectedOper
     * @param string $key
     * @param string $expectedKey
     * @throws MapperException
     * @dataProvider operationsProvider
     */
    public function testOperations(string $operation, $key, string $expectedOper, string $expectedKey): void
    {
        $lib = new Dialects\EmptyDialect();
        $this->assertEquals($expectedOper, $lib->translateOperation($operation));
    }

    /**
     * @param string $operation
     * @param string $expectedOper
     * @param string $key
     * @param string $expectedKey
     * @throws MapperException
     * @dataProvider operationsProvider
     */
    public function testKeys(string $operation, $key, string $expectedOper, string $expectedKey): void
    {
        $lib = new Dialects\EmptyDialect();
        $this->assertEquals($expectedKey, $lib->translateKey($operation, $key));
    }

    public function operationsProvider(): array
    {
        return [
            [IQueryBuilder::OPERATION_NULL, 'abc', 'IS NULL', ''],
            [IQueryBuilder::OPERATION_NNULL, 123, 'IS NOT NULL', ''],
            [IQueryBuilder::OPERATION_EQ, 'def', '=', 'def'],
            [IQueryBuilder::OPERATION_NEQ, 456, '!=', '456'],
            [IQueryBuilder::OPERATION_GT, 'ghi', '>', 'ghi'],
            [IQueryBuilder::OPERATION_GTE, 789.01, '>=', '789.01'],
            [IQueryBuilder::OPERATION_LT, 'jkl', '<', 'jkl'],
            [IQueryBuilder::OPERATION_LTE, new \StrObjMock(), '<=', 'strObj'],
            [IQueryBuilder::OPERATION_LIKE, 'mno', 'LIKE', 'mno'],
            [IQueryBuilder::OPERATION_NLIKE, 'pqr', 'NOT LIKE', 'pqr'],
            [IQueryBuilder::OPERATION_REXP, 'stu', 'REGEX', 'stu'],
            [IQueryBuilder::OPERATION_IN,  [], 'IN', '(0)'],
            [IQueryBuilder::OPERATION_NIN, ['okm', 'ijn'], 'NOT IN', '(okm,ijn)'],
        ];
    }

    public function testOperationsFailed(): void
    {
        $lib = new Dialects\EmptyDialect();
        $this->expectException(MapperException::class);
        $lib->translateOperation('undefined one');
    }

    public function testKeysFailed(): void
    {
        $lib = new Dialects\EmptyDialect();
        $this->expectException(MapperException::class);
        $lib->translateKey('undefined one', 'with failed');
    }

    public function testAllColumns(): void
    {
        $lib = new Dialects\EmptyDialect();
        $this->assertEquals('*', $lib->makeColumns([]));
    }

    public function testSelectedColumns(): void
    {
        $column1 = new QueryBuilder\Column();
        $column1->setData('abc', 'def', 'ghi', 'jkl');

        $column2 = new QueryBuilder\Column();
        $column2->setData('', 'pqr', 'stu');

        $columns = [];
        $columns[] = $column1;
        $columns[] = $column2;

        $lib = new Dialects\EmptyDialect();
        $this->assertEquals('jkl(abc.def) AS ghi, pqr AS stu', $lib->makeColumns($columns));
    }

    public function testAllProperties(): void
    {
        $lib = new Dialects\EmptyDialect();
        $this->assertEquals('1=1', $lib->makeProperty([]));
    }

    public function testSelectedProperties(): void
    {
        $property1 = new QueryBuilder\Property();
        $property1->setData('abc', 'def', 'ghi');

        $property2 = new QueryBuilder\Property();
        $property2->setData('jkl', 'mno', 'pqr');

        $properties = [];
        $properties[] = $property1;
        $properties[] = $property2;

        $lib = new Dialects\EmptyDialect();
        $this->assertEquals('def = ghi, mno = pqr', $lib->makeProperty($properties));
    }

    /**
     * @throws MapperException
     */
    public function testAllPropertiesAsList(): void
    {
        $lib = new Dialects\EmptyDialect();
        $this->expectException(MapperException::class);
        $lib->makePropertyList([]);
    }

    /**
     * @throws MapperException
     */
    public function testAllPropertiesAsEntries(): void
    {
        $lib = new Dialects\EmptyDialect();
        $this->expectException(MapperException::class);
        $lib->makePropertyEntries([]);
    }

    /**
     * @throws MapperException
     */
    public function testSelectedPropertiesAsList(): void
    {
        $property1 = new QueryBuilder\Property();
        $property1->setData('abc', 'def', 'ghi');

        $property2 = new QueryBuilder\Property();
        $property2->setData('', 'mno', 'pqr');

        $properties = [];
        $properties[] = $property1;
        $properties[] = $property2;

        $lib = new Dialects\EmptyDialect();
        $this->assertEquals('abc.def, mno', $lib->makePropertyList($properties));
        $this->assertEquals('ghi, pqr', $lib->makePropertyEntries($properties));
    }

    public function testAllConditions(): void
    {
        $lib = new Dialects\EmptyDialect();
        $this->assertEquals('', $lib->makeConditions([], 'not need for this'));
    }

    public function testSelectedConditions(): void
    {
        $condition1 = new QueryBuilder\Condition();
        $condition1->setData('abc', 'def', IQueryBuilder::OPERATION_EQ, 'ghi');

        $condition2 = new QueryBuilder\Condition();
        $condition2->setData('', 'mno', IQueryBuilder::OPERATION_NEQ, 'pqr');

        $conditions = [];
        $conditions[] = $condition1;
        $conditions[] = $condition2;

        $lib = new Dialects\EmptyDialect();
        $this->assertEquals(' WHERE abc.def = ghi UNIONED BY mno != pqr', $lib->makeConditions($conditions, 'UNIONED BY'));
    }

    public function testAllOrdering(): void
    {
        $lib = new Dialects\EmptyDialect();
        $this->assertEquals('', $lib->makeOrdering([]));
    }

    public function testSelectedOrdering(): void
    {
        $order1 = new QueryBuilder\Order();
        $order1->setData('abc', 'def', 'ghi');

        $order2 = new QueryBuilder\Order();
        $order2->setData('', 'mno', 'pqr');

        $ordering = [];
        $ordering[] = $order1;
        $ordering[] = $order2;

        $lib = new Dialects\EmptyDialect();
        $this->assertEquals(' ORDER BY abc.def ghi, mno pqr', $lib->makeOrdering($ordering));
    }

    public function testAllGrouping(): void
    {
        $lib = new Dialects\EmptyDialect();
        $this->assertEquals('', $lib->makeGrouping([]));
    }

    public function testSelectedGrouping(): void
    {
        $group1 = new QueryBuilder\Group();
        $group1->setData('abc', 'def');

        $group2 = new QueryBuilder\Group();
        $group2->setData('', 'ghi');

        $grouping = [];
        $grouping[] = $group1;
        $grouping[] = $group2;

        $lib = new Dialects\EmptyDialect();
        $this->assertEquals(' GROUP BY abc.def, ghi', $lib->makeGrouping($grouping));
    }

    public function testAllJoins(): void
    {
        $lib = new Dialects\EmptyDialect();
        $this->assertEquals('', $lib->makeJoin([]));
    }

    public function testSelectedJoins(): void
    {
        $group1 = new QueryBuilder\Join();
        $group1->setData('abc', 'def', 'ghi', 'jkl', 'mno', 'pqr', 'stu');

        $group2 = new QueryBuilder\Join();
        $group2->setData('vwx', 'yza', 'bcd', 'efg', 'hij', 'klm');

        $grouping = [];
        $grouping[] = $group1;
        $grouping[] = $group2;

        $lib = new Dialects\EmptyDialect();
        $this->assertEquals(' pqr JOIN def AS stu ON (jkl.mno = stu.ghi)  klm JOIN yza ON (efg.hij = yza.bcd)', $lib->makeJoin($grouping));
    }
}

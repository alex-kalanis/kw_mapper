<?php

namespace StorageTests\Database\Dialects;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Dialects;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


class QuotationDialectTest extends CommonTestClass
{
    public function testAllColumns(): void
    {
        $lib = new XQuoteDialect();
        $this->assertEquals('*', $lib->makeSimpleColumns([]));
        $this->assertEquals('*', $lib->makeFullColumns([]));
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

        $lib = new XQuoteDialect();
        $this->assertEquals('jkl("def") AS "ghi", "pqr" AS "stu"', $lib->makeSimpleColumns($columns));
        $this->assertEquals('jkl("abc"."def") AS "ghi", "pqr" AS "stu"', $lib->makeFullColumns($columns));
    }

    public function testAllProperties(): void
    {
        $lib = new XQuoteDialect();
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

        $lib = new XQuoteDialect();
        $this->assertEquals('"def" = ghi, "mno" = pqr', $lib->makeProperty($properties));
    }

    /**
     * @throws MapperException
     */
    public function testAllPropertiesAsSimpleList(): void
    {
        $lib = new XQuoteDialect();
        $this->expectException(MapperException::class);
        $lib->makeSimplePropertyList([]);
    }

    /**
     * @throws MapperException
     */
    public function testAllPropertiesAsFullList(): void
    {
        $lib = new XQuoteDialect();
        $this->expectException(MapperException::class);
        $lib->makeFullPropertyList([]);
    }

    /**
     * @throws MapperException
     */
    public function testAllPropertiesAsEntries(): void
    {
        $lib = new XQuoteDialect();
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

        $lib = new XQuoteDialect();
        $this->assertEquals('"def", "mno"', $lib->makeSimplePropertyList($properties));
        $this->assertEquals('"abc"."def", "mno"', $lib->makeFullPropertyList($properties));
        $this->assertEquals('ghi, pqr', $lib->makePropertyEntries($properties));
    }

    public function testAllConditions(): void
    {
        $lib = new XQuoteDialect();
        $this->assertEquals('', $lib->makeSimpleConditions([], 'not need for this'));
        $this->assertEquals('', $lib->makeFullConditions([], 'not need for this'));
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

        $lib = new XQuoteDialect();
        $this->assertEquals(' WHERE "def" = ghi UNIONED BY "mno" != pqr', $lib->makeSimpleConditions($conditions, 'UNIONED BY'));
        $this->assertEquals(' WHERE "abc"."def" = ghi UNIONED BY "mno" != pqr', $lib->makeFullConditions($conditions, 'UNIONED BY'));
    }

    public function testAllOrdering(): void
    {
        $lib = new XQuoteDialect();
        $this->assertEquals('', $lib->makeSimpleOrdering([]));
        $this->assertEquals('', $lib->makeFullOrdering([]));
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

        $lib = new XQuoteDialect();
        $this->assertEquals(' ORDER BY "def" ghi, "mno" pqr', $lib->makeSimpleOrdering($ordering));
        $this->assertEquals(' ORDER BY "abc"."def" ghi, "mno" pqr', $lib->makeFullOrdering($ordering));
    }

    public function testAllGrouping(): void
    {
        $lib = new XQuoteDialect();
        $this->assertEquals('', $lib->makeSimpleGrouping([]));
        $this->assertEquals('', $lib->makeFullGrouping([]));
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

        $lib = new XQuoteDialect();
        $this->assertEquals(' GROUP BY "def", "ghi"', $lib->makeSimpleGrouping($grouping));
        $this->assertEquals(' GROUP BY "abc"."def", "ghi"', $lib->makeFullGrouping($grouping));
    }

    public function testAllHaving(): void
    {
        $lib = new XQuoteDialect();
        $this->assertEquals('', $lib->makeSimpleHaving([], 'not need for this case'));
        $this->assertEquals('', $lib->makeFullHaving([], 'not need for this case'));
    }

    public function testSelectedHaving(): void
    {
        $condition1 = new QueryBuilder\Condition();
        $condition1->setData('abc', 'def', IQueryBuilder::OPERATION_EQ, 'ghi');

        $condition2 = new QueryBuilder\Condition();
        $condition2->setData('', 'mno', IQueryBuilder::OPERATION_NEQ, 'pqr');

        $conditions = [];
        $conditions[] = $condition1;
        $conditions[] = $condition2;

        $lib = new XQuoteDialect();
        $this->assertEquals(' HAVING "def" = ghi KNOWN BY "mno" != pqr', $lib->makeSimpleHaving($conditions, 'KNOWN BY'));
        $this->assertEquals(' HAVING "abc"."def" = ghi KNOWN BY "mno" != pqr', $lib->makeFullHaving($conditions, 'KNOWN BY'));
    }

    public function testAllJoins(): void
    {
        $lib = new XQuoteDialect();
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

        $lib = new XQuoteDialect();
        $this->assertEquals(' pqr JOIN "def" AS "stu" ON ("jkl"."mno" = "stu"."ghi")  klm JOIN "yza" ON ("efg"."hij" = "yza"."bcd")', $lib->makeJoin($grouping));
    }
}


class XQuoteDialect extends Dialects\EmptyDialect
{
    use Dialects\TQuotationDialect;
}

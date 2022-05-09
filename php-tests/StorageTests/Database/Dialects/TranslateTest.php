<?php

namespace StorageTests\Database\Dialects;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Dialects;


class TranslateTest extends CommonTestClass
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
        $lib = new XTranslate();
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
        $lib = new XTranslate();
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

    /**
     * @throws MapperException
     */
    public function testOperationsFailed(): void
    {
        $lib = new XTranslate();
        $this->expectException(MapperException::class);
        $lib->translateOperation('undefined one');
    }

    /**
     * @throws MapperException
     */
    public function testKeysFailed(): void
    {
        $lib = new XTranslate();
        $this->expectException(MapperException::class);
        $lib->translateKey('undefined one', 'with failed');
    }
}


class XTranslate
{
    use Dialects\TTranslate;
}

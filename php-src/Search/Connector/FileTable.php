<?php

namespace kalanis\kw_mapper\Search\Connector;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Storage;


/**
 * Class FileTable
 * @package kalanis\kw_mapper\Search
 * Connect file containing table as datasource
 */
class FileTable extends Records
{
    public function child(string $childAlias, string $joinType = IQueryBuilder::JOIN_LEFT, string $parentAlias = '', string $customAlias = ''): AConnector
    {
        throw new MapperException('Cannot make relations over files!');
    }

    public function childNotExist(string $childAlias, string $table, string $column, string $parentAlias = ''): AConnector
    {
        throw new MapperException('Cannot make relations over files!');
    }

    public function childTree(string $childAlias): array
    {
        throw new MapperException('Cannot access relations over files!');
    }

    /**
     * @return ARecord[]
     * @throws MapperException
     */
    protected function getInitialRecords(): array
    {
        return $this->basicRecord->loadMultiple();
    }
}

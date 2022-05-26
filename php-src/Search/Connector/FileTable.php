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
        // @todo idea: how it will work
        //      - from the most far ones load records and filter them by other params
        //      - them continue back and compare already available join params defined for each pair
        //      - the foremost records will be only that which has been available by previously selected ones
        //    De facto do the full table engine inside the php
        throw new MapperException('Cannot make relations over files!');
    }

    public function childNotExist(string $childAlias, string $table, string $column, string $parentAlias = ''): AConnector
    {
        throw new MapperException('Cannot make relations over files!');
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

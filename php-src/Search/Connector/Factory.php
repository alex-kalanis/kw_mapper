<?php

namespace kalanis\kw_mapper\Search\Connector;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records\ARecord;


/**
 * Class Factory
 * @package kalanis\kw_mapper\Search
 * Complex searching
 */
class Factory
{
    public static function getInstance(): self
    {
        return new static();
    }

    /**
     * @param ARecord $record
     * @param ARecord[] $initialRecords
     * @return AConnector
     * @throws MapperException
     */
    public function getConnector(ARecord $record, array $initialRecords = []): AConnector
    {
        $mapper = $record->getMapper();
        if ($mapper instanceof Mappers\Database\ADatabase) {
            return new Database($record);
        } elseif ($mapper instanceof Mappers\File\ATable) {
            return new FileTable($record);
        } elseif (!empty($initialRecords)) {
            $records = new Records($record);
            $records->setInitialRecords($initialRecords);
            return $records;
        } else {
            throw new MapperException('Invalid mapper.');
        }
    }
}

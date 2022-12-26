<?php

namespace kalanis\kw_mapper\Search\Connector;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Class Factory
 * @package kalanis\kw_mapper\Search
 * Complex searching - factory for access correct connecting classes
 */
class Factory
{
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * @param ARecord $record
     * @param ARecord[] $initialRecords
     * @param QueryBuilder|null $builder
     * @throws MapperException
     * @return AConnector
     */
    public function getConnector(ARecord $record, array $initialRecords = [], ?QueryBuilder $builder = null): AConnector
    {
        $mapper = $record->getMapper();
        if ($mapper instanceof Mappers\Database\ADatabase) {
            return new Database($record, $builder);
        } elseif ($mapper instanceof Mappers\Database\ALdap) {
            // @codeCoverageIgnoreStart
            return new Ldap($record, $builder);
            // @codeCoverageIgnoreEnd
        } elseif ($mapper instanceof Mappers\Database\WinRegistry) {
            // @codeCoverageIgnoreStart
            return new WinRegistry($record, $builder);
            // @codeCoverageIgnoreEnd
        } elseif ($mapper instanceof Mappers\Storage\ATable) {
            return new FileTable($record);
        } elseif ($mapper instanceof Mappers\File\ATable) {
            return new FileTable($record);
        } elseif ($mapper instanceof Mappers\APreset) {
            return new FileTable($record);
        } elseif (!empty($initialRecords)) {
            $records = new Records($record);
            $records->setInitialRecords($initialRecords);
            return $records;
        } else {
            throw new MapperException('Invalid mapper for Search.');
        }
    }
}

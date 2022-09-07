<?php

namespace kalanis\kw_mapper\Mappers;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records;


/**
 * Class APreset
 * @package kalanis\kw_mapper\Mappers
 * Abstract for manipulation with constant content as table
 *
 * You just need to extend this class and set datasource array and correct map.
 */
abstract class APreset extends AMapper
{
    use TFinder;

    public function getAlias(): string
    {
        return $this->getSource();
    }

    protected function insertRecord(Records\ARecord $record): bool
    {
        throw new MapperException('Cannot insert record into predefined array');
    }

    protected function updateRecord(Records\ARecord $record): bool
    {
        throw new MapperException('Cannot update record in predefined array');
    }

    /**
     * @param Records\ARecord|Records\PageRecord $record
     * @throws MapperException
     * @return int
     */
    public function countRecord(Records\ARecord $record): int
    {
        $matches = $this->findMatched($record);
        return count($matches);
    }

    /**
     * @param Records\ARecord|Records\PageRecord $record
     * @throws MapperException
     * @return bool
     */
    protected function loadRecord(Records\ARecord $record): bool
    {
        $matches = $this->findMatched($record);
        if (empty($matches)) { // nothing found
            return false;
        }

        $dataLine = & $this->records[reset($matches)];
        foreach ($this->relations as $objectKey => $recordKey) {
            $entry = $record->getEntry($objectKey);
            $entry->setData($dataLine->offsetGet($objectKey), true);
        }
        return true;
    }

    protected function deleteRecord(Records\ARecord $record): bool
    {
        throw new MapperException('Cannot delete record in predefined array');
    }

    /**
     * @param Records\ARecord $record
     * @throws MapperException
     * @return Records\ARecord[]
     */
    public function loadMultiple(Records\ARecord $record): array
    {
        $toLoad = $this->findMatched($record);

        $result = [];
        foreach ($toLoad as $key) {
            $result[] = $this->records[$key];
        }
        return $result;
    }
}

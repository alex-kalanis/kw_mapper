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
    use TStore;

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
        return count($this->findMatched($record));
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

        reset($matches);
        $dataLine = & $this->records[key($matches)];
        foreach ($this->getRelations() as $objectKey => $recordKey) {
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
        return $this->findMatched($record);
    }

    /**
     * @param array<string|int, string|int|float|array<string|int, string|int|array<string|int, string|int>>> $content
     * @throws MapperException
     * @return bool
     * @codeCoverageIgnore should not be accessible
     */
    protected function saveToStorage(array $content): bool
    {
        throw new MapperException('Cannot save records in predefined array');
    }
}

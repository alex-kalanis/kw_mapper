<?php

namespace kalanis\kw_mapper\Mappers\Shared;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records;


/**
 * Trait TReadFileTable
 * @package kalanis\kw_mapper\Mappers\Shared
 * Abstract for manipulation with file content as table - read content
 */
trait TReadFileTable
{
    use TFinder;
    use TStore;

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
        $this->clearSource();
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

    /**
     * @param Records\ARecord $record
     * @throws MapperException
     * @return Records\ARecord[]
     */
    public function loadMultiple(Records\ARecord $record): array
    {
        return array_values($this->findMatched($record));
    }
}

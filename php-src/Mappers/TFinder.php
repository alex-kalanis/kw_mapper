<?php

namespace kalanis\kw_mapper\Mappers;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records;


/**
 * Trait TFinder
 * @package kalanis\kw_mapper\Mappers
 * Abstract for manipulation with file content as table
 */
trait TFinder
{
    use TTranslate;
    use TRelations;
    use TPrimaryKey;

    /** @var Records\ARecord[] */
    protected $records = [];

    /**
     * @param Records\ARecord $record
     * @param bool $usePks
     * @param bool $wantFromStorage
     * @throws MapperException
     * @return string[]|int[]
     */
    protected function findMatched(Records\ARecord $record, bool $usePks = false, bool $wantFromStorage = false): array
    {
        $this->loadOnDemand($record);

        $toProcess = array_keys($this->records);
        $toProcess = array_combine($toProcess, $toProcess);

        // through relations
        foreach ($this->relations as $objectKey => $recordKey) {
            if (!$record->offsetExists($objectKey)) { // nothing with unknown relation key in record
                // @codeCoverageIgnoreStart
                if ($usePks && in_array($objectKey, $this->primaryKeys)) { // is empty PK
                    return []; // probably error?
                }
                continue;
                // @codeCoverageIgnoreEnd
            }
            if (empty($record->offsetGet($objectKey))) { // nothing with empty data
                if ($usePks && in_array($objectKey, $this->primaryKeys)) { // is empty PK
                    return [];
                }
                continue;
            }

            foreach ($this->records as $knownKey => $knownRecord) {
                if ( !isset($toProcess[$knownKey]) ) { // not twice
                    continue;
                }
                if ($usePks && !in_array($objectKey, $this->primaryKeys)) { // is not PK
                    continue;
                }
                if ($wantFromStorage && !$knownRecord->getEntry($objectKey)->isFromStorage()) { // look through only values known in storage
                    continue;
                }
                if ( !$knownRecord->offsetExists($objectKey) ) { // unknown relation key in record is not allowed into compare
                    // @codeCoverageIgnoreStart
                    unset($toProcess[$knownKey]);
                    continue;
                }
                // @codeCoverageIgnoreEnd
                if ( empty($knownRecord->offsetGet($objectKey)) ) { // empty input is not need to compare
                    unset($toProcess[$knownKey]);
                    continue;
                }
                if ( strval($knownRecord->offsetGet($objectKey)) != strval($record->offsetGet($objectKey)) ) {
                    unset($toProcess[$knownKey]);
                    continue;
                }
            }
        }

        return $toProcess;
    }

    /**
     * More records on one mapper - reload with correct one
     * @param Records\ARecord $record
     * @throws MapperException
     */
    protected function loadOnDemand(Records\ARecord $record): void
    {
        if (empty($this->records)) {
            $this->loadSource($record);
        } else {
            $test = reset($this->records);
            if (get_class($test) != get_class($record)) { // reload other data - changed record
                // @codeCoverageIgnoreStart
                $this->loadSource($record);
            }
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @param Records\ARecord $record
     * @throws MapperException
     * @return Records\ARecord[]
     */
    protected function loadSource(Records\ARecord $record): array
    {
        $lines = $this->loadFromStorage();
        $records = [];
        foreach ($lines as &$line) {

            $item = clone $record;

            foreach ($this->relations as $objectKey => $recordKey) {
                $entry = $item->getEntry($objectKey);
                $entry->setData($this->translateTypeFrom($entry->getType(), $line[$recordKey]), true);
            }
            $records[] = $item;
        }
        return $records;
    }

    /**
     * @return array<string|int, string|int|float|array<string|int, string|int|array<string|int, string|int>>>
     */
    abstract protected function loadFromStorage(): array;
}

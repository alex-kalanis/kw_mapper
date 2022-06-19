<?php

namespace kalanis\kw_mapper\Mappers\File;


use kalanis\kw_mapper\Adapters\DataExchange;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records;


/**
 * Class ATable
 * @package kalanis\kw_mapper\Mappers\File
 * Abstract for manipulation with file content as table
 */
abstract class ATable extends AStorage
{
    use TTranslate;

    /** @var bool */
    protected $orderFromFirst = true;

    /** @var Records\ARecord[] */
    protected $records = [];

    public function orderFromFirst(bool $orderFromFirst = true): self
    {
        $this->orderFromFirst = $orderFromFirst;
        return $this;
    }

    /**
     * @param Records\ARecord|Records\PageRecord $record
     * @throws MapperException
     * @return bool
     */
    protected function insertRecord(Records\ARecord $record): bool
    {
        $matches = $this->findMatched($record, !empty($this->primaryKeys));
        if (!empty($matches)) { // found!!!
            return false;
        }

        // pks
        $records = array_map([$this, 'toArray'], $this->records);
        foreach ($this->primaryKeys as $primaryKey) {
            $entry = $record->getEntry($primaryKey);
            if (in_array($entry->getType(), [IEntryType::TYPE_INTEGER, IEntryType::TYPE_FLOAT])) {
                if (empty($entry->getData())) {
                    $data = empty($records) ? 1 : intval(max(array_column($records, $primaryKey))) + 1 ;
                    $entry->setData($data);
                }
            }
        }

        $this->records = $this->orderFromFirst ? array_merge($this->records, [$record]) : array_merge([$record], $this->records);
        return $this->saveSource();
    }

    /**
     * @param Records\ARecord $object
     * @return array<string|int, string|int|float|object|array<string|int|float|object>>
     */
    public function toArray($object)
    {
        $ex = new DataExchange($object);
        return $ex->export();
    }

    /**
     * @param Records\ARecord|Records\PageRecord $record
     * @throws MapperException
     * @return bool
     */
    protected function updateRecord(Records\ARecord $record): bool
    {
        $matches = $this->findMatched($record, !empty($this->primaryKeys), true);
        if (empty($matches)) { // nothing found
            return false;
        }

        $dataLine = & $this->records[reset($matches)];
        foreach ($this->relations as $objectKey => $recordKey) {
            if (in_array($objectKey, $this->primaryKeys)) {
                continue; // no to change pks
            }
            $dataLine->offsetSet($objectKey, $record->offsetGet($objectKey));
        }
        return $this->saveSource();
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

    /**
     * @param Records\ARecord|Records\PageRecord $record
     * @throws MapperException
     * @return bool
     * Scan array and remove items that have set equal values as that in passed record
     */
    protected function deleteRecord(Records\ARecord $record): bool
    {
        $toDelete = $this->findMatched($record);
        if (empty($toDelete)) {
            return false;
        }

        // remove matched
        foreach ($toDelete as $key) {
            unset($this->records[$key]);
        }
        return $this->saveSource();
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

    /**
     * @param Records\ARecord $record
     * @param bool $usePks
     * @param bool $wantFromStorage
     * @throws MapperException
     * @return string[]|int[]
     */
    private function findMatched(Records\ARecord $record, bool $usePks = false, bool $wantFromStorage = false): array
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
    private function loadOnDemand(Records\ARecord $record): void
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
     */
    private function loadSource(Records\ARecord $record): void
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
        $this->records = $records;
    }

    /**
     * @throws MapperException
     * @return bool
     */
    private function saveSource(): bool
    {
        $lines = [];
        foreach ($this->records as &$record) {
            $dataLine = [];

            foreach ($this->relations as $objectKey => $recordKey) {
                $entry = $record->getEntry($objectKey);
                $dataLine[$recordKey] = $this->translateTypeTo($entry->getType(), $entry->getData());
            }

            $linePk = $this->generateKeyFromPks($record);
            if ($linePk) {
                $lines[$linePk] = $dataLine;
            } else {
                $lines[] = $dataLine;
            }
        }
        return $this->saveToStorage($lines);
    }

    /**
     * @param Records\ARecord $record
     * @throws MapperException
     * @return string|null
     */
    private function generateKeyFromPks(Records\ARecord $record): ?string
    {
        $toComplete = [];
        foreach ($this->primaryKeys as $key) {
            $toComplete[] = $record->offsetGet($key);
        }
        return (count(array_filter($toComplete))) ? implode('_', $toComplete) : null ;
    }
}

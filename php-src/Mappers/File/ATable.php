<?php

namespace kalanis\kw_mapper\Mappers\File;


use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records;


/**
 * Class ATable
 * @package kalanis\kw_mapper\Mappers\File
 * Abstract for manipulation with file content as table
 */
abstract class ATable extends AFile
{
    use TTranslate;

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
     * @return bool
     * @throws MapperException
     */
    protected function insertRecord(Records\ARecord $record): bool
    {
        $matches = $this->findMatched($record, true);
        if (!empty($matches)) { // found!!!
            return false;
        }

        // pks
        foreach ($this->primaryKeys as $primaryKey) {
            $entry = $record->getEntry($primaryKey);
            if (in_array($entry->getType(), [IEntryType::TYPE_INTEGER, IEntryType::TYPE_FLOAT])) {
                $data = max(array_column($this->records, $primaryKey)) + 1;
                $entry->setData($data);
            }
        }

        $this->records = $this->orderFromFirst ? $this->records + [$record] : [$record] + $this->records;
        return $this->saveSource();
    }

    /**
     * @param Records\ARecord|Records\PageRecord $record
     * @return bool
     * @throws MapperException
     */
    protected function updateRecord(Records\ARecord $record): bool
    {
        $matches = $this->findMatched($record, true);
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
     * @return int
     * @throws MapperException
     */
    public function countRecord(Records\ARecord $record): int
    {
        $matches = $this->findMatched($record, true);
        return count($matches);
    }

    /**
     * @param Records\ARecord|Records\PageRecord $record
     * @return bool
     * @throws MapperException
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
     * @return bool
     * @throws MapperException
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
     * @return Records\ARecord[]
     * @throws MapperException
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
     * @return string[]|int[]
     * @throws MapperException
     */
    private function findMatched(Records\ARecord $record, bool $usePks = false): array
    {
        $this->loadOnDemand($record);

        $toProcess = array_keys($this->records);
        $toProcess = array_combine($toProcess, $toProcess);

        // through relations
        foreach ($this->relations as $objectKey => $recordKey) {
            if ($usePks && !in_array($objectKey, $this->primaryKeys)) { // is not PK
                continue;
            }

            if (!$record->offsetExists($objectKey)) { // nothing with unknown data
                continue;
            }
            if (empty($record->offsetGet($objectKey))) { // nothing with empty data
                continue;
            }
            foreach ($this->records as $knownKey => $knownRecord) {
                if ( !isset($toProcess[$knownKey]) ) { // not twice
                    continue;
                }
                if ( !$knownRecord->offsetExists($objectKey) ) { // empty is not need to compare
                    unset($toProcess[$knownKey]);
                    continue;
                }
                if ( empty($knownRecord->offsetGet($objectKey)) ) { // empty is not need to compare
                    unset($toProcess[$knownKey]);
                    continue;
                }
                if ( $knownRecord->offsetGet($objectKey) != $record->offsetGet($objectKey) ) {
                    unset($toProcess[$knownKey]);
                }
            }
        }

        return $toProcess;
    }

    /**
     * @param Records\ARecord $record
     * @throws MapperException
     */
    private function loadOnDemand(Records\ARecord $record): void
    {
        if (empty($this->records)) {
            $this->loadSource($record);
        } else {
            $test = reset($this->records);
            if (get_class($test) != get_class($record)) { // reload other data
                $this->loadSource($record);
            }
        }
    }

    /**
     * @param Records\ARecord $record
     * @throws MapperException
     */
    private function loadSource(Records\ARecord $record): void
    {
        $lines = $this->loadFromRemoteSource();
        $records = [];
        foreach ($lines as &$line) {

            $item = clone $record;
            if (!$this->beforeLoad($item)) {
                continue;
            }

            foreach ($this->relations as $objectKey => $recordKey) {
                $entry = $item->getEntry($objectKey);
                $entry->setData($this->translateTypeFrom($entry->getType(), $line[$recordKey]), true);
            }
            if (!$this->afterLoad($item)) {
                continue;
            }

            $records[] = $item;
        }
        $this->records = $records;
    }

    /**
     * @return bool
     * @throws MapperException
     */
    private function saveSource(): bool
    {
        $lines = [];
        foreach ($this->records as &$record) {
            $dataLine = [];

            if (!$this->beforeSave($record)) {
                continue;
            }

            foreach ($this->relations as $objectKey => $recordKey) {
                $entry = $record->getEntry($objectKey);
                $dataLine[$recordKey] = $this->translateTypeTo($entry->getType(), $entry->getData());
            }

            if (!$this->afterSave($record)) {
                continue;
            }

            $pk_key = $this->generateKeyFromPks($record);
            if ($pk_key) {
                $lines[$pk_key] = $dataLine;
            } else {
                $lines[] = $dataLine;
            }
        }
        return $this->saveToRemoteSource($lines);
    }

    /**
     * @param Records\ARecord $record
     * @return string|null
     * @throws MapperException
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

<?php

namespace kalanis\kw_mapper\Mappers\File;


use kalanis\kw_mapper\Adapters\DataExchange;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\TFinder;
use kalanis\kw_mapper\Records;


/**
 * Class ATable
 * @package kalanis\kw_mapper\Mappers\File
 * Abstract for manipulation with file content as table
 */
abstract class ATable extends AStorage
{
    use TFinder;

    /** @var bool */
    protected $orderFromFirst = true;

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

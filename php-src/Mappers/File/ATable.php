<?php

namespace kalanis\kw_mapper\Mappers\File;


use kalanis\kw_mapper\Adapters\DataExchange;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\TFinder;
use kalanis\kw_mapper\Mappers\TStore;
use kalanis\kw_mapper\Mappers\TTranslate;
use kalanis\kw_mapper\Records;


/**
 * Class ATable
 * @package kalanis\kw_mapper\Mappers\File
 * Abstract for manipulation with file content as table
 */
abstract class ATable extends AStorage
{
    use TFinder;
    use TStore;
    use TTranslate;

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
        $matches = $this->findMatched($record, !empty($this->getPrimaryKeys()));
        if (!empty($matches)) { // found!!!
            return false;
        }

        // pks
        $records = array_map([$this, 'toArray'], $this->records);
        foreach ($this->getPrimaryKeys() as $primaryKey) {
            $entry = $record->getEntry($primaryKey);
            if (in_array($entry->getType(), [IEntryType::TYPE_INTEGER, IEntryType::TYPE_FLOAT])) {
                if (empty($entry->getData())) {
                    $data = empty($records) ? 1 : intval(max(array_column($records, $primaryKey))) + 1 ;
                    $entry->setData($data);
                }
            }
        }

        $this->records = $this->orderFromFirst ? array_merge($this->records, [$record]) : array_merge([$record], $this->records);
        return $this->saveSource($this->records);
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
        $matches = $this->findMatched($record, !empty($this->getPrimaryKeys()), true);
        if (empty($matches)) { // nothing found
            return false;
        }

        reset($matches);
        $dataLine = & $this->records[key($matches)];
        foreach ($this->getRelations() as $objectKey => $recordKey) {
            if (in_array($objectKey, $this->getPrimaryKeys())) {
                continue; // no to change pks
            }
            $dataLine->offsetSet($objectKey, $record->offsetGet($objectKey));
        }
        return $this->saveSource($this->records);
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
        foreach ($toDelete as $key => $record) {
            unset($this->records[$key]);
        }
        return $this->saveSource($this->records);
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

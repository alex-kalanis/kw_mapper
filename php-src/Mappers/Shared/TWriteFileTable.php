<?php

namespace kalanis\kw_mapper\Mappers\Shared;


use kalanis\kw_mapper\Adapters\DataExchange;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records;


/**
 * Trait TWriteFileTable
 * @package kalanis\kw_mapper\Mappers\Shared
 * Abstract for manipulation with file content as table - write content
 */
trait TWriteFileTable
{
    use TFinder;
    use TStore;

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
        $this->clearSource();
        $matches = $this->findMatched($record, !empty($this->getPrimaryKeys()));
        if (!empty($matches)) { // already found!!!
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
        $this->clearSource();
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
     * @return bool
     * Scan array and remove items that have set equal values as these in passed record
     */
    protected function deleteRecord(Records\ARecord $record): bool
    {
        $this->clearSource();
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
}

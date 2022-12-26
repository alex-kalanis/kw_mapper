<?php

namespace kalanis\kw_mapper\Mappers;


use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;


/**
 * Class AMapper
 * @package kalanis\kw_mapper\Mappers
 * Basic things you want to mapper do
 */
abstract class AMapper
{
    use Shared\TForeignKey;
    use Shared\TPrimaryKey;
    use Shared\TRelations;
    use Shared\TSource;

    /**
     * @throws MapperException
     */
    public function __construct()
    {
        $this->setMap();
    }

    /**
     * Set which record maps on which on service
     * @throws MapperException
     */
    abstract protected function setMap(): void;

    /**
     * Alias under which is mapper available in the heap of the others
     * @return string
     */
    abstract public function getAlias(): string;

    /**
     * Things to do before saving record
     * @param ARecord $record
     * @return bool
     */
    protected function beforeSave(ARecord $record): bool
    {
        return true;
    }

    /**
     * Things to do after saving record
     * @param ARecord $record
     * @return bool
     */
    protected function afterSave(ARecord $record): bool
    {
        return true;
    }

    /**
     * Things to do before deleting record with params
     * @param ARecord $record
     * @return bool
     */
    protected function beforeDelete(ARecord $record): bool
    {
        return true;
    }

    /**
     * Things to do after deleting record with params
     * @param ARecord $record
     * @return bool
     */
    protected function afterDelete(ARecord $record): bool
    {
        return true;
    }

    /**
     * Things to do before updating record
     * @param ARecord $record
     * @return bool
     */
    protected function beforeUpdate(ARecord $record): bool
    {
        return true;
    }

    /**
     * Things to do after updating record
     * @param ARecord $record
     * @return bool
     */
    protected function afterUpdate(ARecord $record): bool
    {
        return true;
    }

    /**
     * Things to do before inserting record
     * @param ARecord $record
     * @return bool
     */
    protected function beforeInsert(ARecord $record): bool
    {
        return true;
    }

    /**
     * Things to do after inserting record
     * @param ARecord $record
     * @return bool
     */
    protected function afterInsert(ARecord $record): bool
    {
        return true;
    }

    /**
     * Things to do before loading record
     * @param ARecord $record
     * @return bool
     */
    protected function beforeLoad(ARecord $record): bool
    {
        return true;
    }

    /**
     * Things to do after loading record
     * @param ARecord $record
     * @return bool
     */
    protected function afterLoad(ARecord $record): bool
    {
        return true;
    }

    /**
     * Insert data
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    public function insert(ARecord $record): bool
    {
        if (!$this->beforeInsert($record)) {
            return false;
        }

        if (!$this->insertRecord($record)) {
            return false;
        }

        return $this->afterInsert($record);
    }

    /**
     * Insert data - process
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    abstract protected function insertRecord(ARecord $record): bool;

    /**
     * Update data - by entries in record
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    public function update(ARecord $record): bool
    {
        if (!$this->beforeUpdate($record)) {
            return false;
        }

        if (!$this->updateRecord($record)) {
            return false;
        }

        return $this->afterUpdate($record);
    }

    /**
     * Update data - by entries in record - process
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    abstract protected function updateRecord(ARecord $record): bool;

    /**
     * Load record from storage
     * @param ARecord $record
     * @throws MapperException
     * @return boolean
     */
    public function load(ARecord $record): bool
    {
        if (!$this->beforeLoad($record)) {
            return false;
        }

        if (!$this->loadRecord($record)) {
            return false;
        }

        return $this->afterLoad($record);
    }

    /**
     * Count records with equal data as predefined one
     * @param ARecord $record
     * @throws MapperException
     * @return int
     */
    abstract public function countRecord(ARecord $record): int;

    /**
     * Load record from storage
     * @param ARecord $record
     * @throws MapperException
     * @return ARecord[]
     */
    abstract public function loadMultiple(ARecord $record): array;

    /**
     * Load record from storage - process
     * @param ARecord $record
     * @throws MapperException
     * @return boolean
     */
    abstract protected function loadRecord(ARecord $record): bool;

    /**
     * Save record object
     * @param ARecord $record
     * @param bool $forceInsert
     * @throws MapperException
     * @return bool
     */
    public function save(ARecord $record, bool $forceInsert = false): bool
    {
        if (!$this->beforeSave($record)) {
            return false;
        }

        $hasPreset = 0;
        $hasNewOne = 0;
        foreach ($record as $key => $value) {
            $fromStorage = $record->getEntry($key)->isFromStorage();
            $toCompare = $record->getEntry($key)->getData();
            $stay = (IEntryType::TYPE_BOOLEAN == $record->getEntry($key)->getType())
                ? is_null($toCompare)
                : (false === $toCompare)
            ;
            $hasPreset += intval($fromStorage && !$stay);
            $hasNewOne += intval(!$fromStorage && !$stay);
        }

        $result = (boolval($hasPreset) && boolval($hasNewOne) && !$forceInsert)
            ? $this->update($record)
            : $this->insert($record)
        ;

        if (!$result) {
            return false;
        }

        return $this->afterSave($record);
    }

    /**
     * Remove record from storage
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    public function delete(ARecord $record): bool
    {
        if (!$this->beforeDelete($record)) {
            return false;
        }

        if (!$this->deleteRecord($record)) {
            return false;
        }

        return $this->afterDelete($record);
    }

    /**
     * Remove record from storage - process
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    abstract protected function deleteRecord(ARecord $record): bool;
}

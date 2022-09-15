<?php

namespace kalanis\kw_mapper\Mappers\File;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\PageRecord;
use kalanis\kw_storage\StorageException;


/**
 * Class AFile
 * @package kalanis\kw_mapper\Mappers\Database
 * Abstract layer for working with single files as content source
 */
abstract class AFile extends AStorage
{
    use TContent;

    public function setPathKey(string $pathKey): self
    {
        $this->addPrimaryKey($pathKey);
        return $this;
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function insertRecord(ARecord $record): bool
    {
        return $this->updateRecord($record);
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function updateRecord(ARecord $record): bool
    {
        $this->setSource(strval($record->offsetGet($this->getPathFromPk($record))));
        return $this->saveToStorage([$record->offsetGet($this->getContentKey())]);
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return int
     */
    public function countRecord(ARecord $record): int
    {
        $this->setSource(strval($record->offsetGet($this->getPathFromPk($record))));
        return intval(!empty($this->loadFromStorage()));
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function loadRecord(ARecord $record): bool
    {
        $this->setSource(strval($record->offsetGet($this->getPathFromPk($record))));
        $stored = $this->loadFromStorage();
        $record->getEntry($this->getContentKey())->setData(reset($stored), true);
        return true;
    }

    /**
     * @param ARecord|PageRecord $record
     * @throws MapperException
     * @return bool
     */
    protected function deleteRecord(ARecord $record): bool
    {
        $path = strval($record->offsetGet($this->getPathFromPk($record)));
        try {
            if ($this->getStorage()->exists($path)) {
                return $this->getStorage()->remove($path);
            }
        } catch (StorageException $ex) {
            return false;
        }
        return true; // not found - operation successful
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return string
     */
    protected function getPathFromPk(ARecord $record): string
    {
        $pk = reset($this->primaryKeys);
        if (!$pk || empty($record->offsetGet($pk))) {
            throw new MapperException('Cannot manipulate content without primary key - path!');
        }
        return $pk;
    }
}

<?php

namespace kalanis\kw_mapper\Mappers\File;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records;
use kalanis\kw_storage\StorageException;


/**
 * Class PageContent
 * @package kalanis\kw_mapper\Mappers\File
 */
class PageContent extends AFile
{
    use TContent;

    protected function setMap(): void
    {
        $this->setPathKey('path');
        $this->setContentKey('content');
        $this->setFormat('\kalanis\kw_mapper\Storage\File\Formats\SinglePage');
    }

    public function setPathKey(string $pathKey): self
    {
        $this->addPrimaryKey($pathKey);
        return $this;
    }

    /**
     * @param Records\ARecord|Records\PageRecord $record
     * @return bool
     * @throws MapperException
     */
    protected function insertRecord(Records\ARecord $record): bool
    {
        return $this->updateRecord($record);
    }

    /**
     * @param Records\ARecord|Records\PageRecord $record
     * @return bool
     * @throws MapperException
     */
    protected function updateRecord(Records\ARecord $record): bool
    {
        $this->setFile($record->offsetGet($this->getPathFromPk($record)));
        return $this->saveToRemoteSource([$record->offsetGet($this->getContentKey())]);
    }

    /**
     * @param Records\ARecord $record
     * @return int
     * @throws MapperException
     */
    public function countRecord(Records\ARecord $record): int
    {
        $this->setFile($record->offsetGet($this->getPathFromPk($record)));
        return intval(!empty($this->loadFromRemoteSource()));
    }

    /**
     * @param Records\ARecord|Records\PageRecord $record
     * @return bool
     * @throws MapperException
     */
    protected function loadRecord(Records\ARecord $record): bool
    {
        $this->setFile($record->offsetGet($this->getPathFromPk($record)));
        $record->getEntry($this->getContentKey())->setData($this->loadFromRemoteSource(), true);
        return true;
    }

    /**
     * @param Records\ARecord|Records\PageRecord $record
     * @return bool
     * @throws MapperException
     */
    protected function deleteRecord(Records\ARecord $record): bool
    {
        $path = $record->offsetGet($this->getPathFromPk($record));
        try {
            if ($this->getStorage()->exists($path)) {
                return $this->getStorage()->remove($path);
            }
            // @codeCoverageIgnoreStart
            // remote storage
        } catch (StorageException $ex) {
            return false;
        }
        return true; // not found - operation successful
        // @codeCoverageIgnoreEnd
    }

    public function loadMultiple(Records\ARecord $record): array
    {
        $this->load($record);
        return [$record];
    }

    /**
     * @param Records\ARecord $record
     * @return string
     * @throws MapperException
     */
    protected function getPathFromPk(Records\ARecord $record): string
    {
        $pk = reset($this->primaryKeys);
        if (!$pk || empty($record->offsetGet($pk))) {
            throw new MapperException('Cannot manipulate content without primary key - path!');
        }
        return $pk;
    }
}

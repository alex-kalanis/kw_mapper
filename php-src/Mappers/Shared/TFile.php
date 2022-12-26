<?php

namespace kalanis\kw_mapper\Mappers\Shared;


use kalanis\kw_mapper\Interfaces\IFileFormat;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;


/**
 * Trait TFile
 * @package kalanis\kw_mapper\Mappers\Storage
 * Abstract layer for working with single files as content source
 */
trait TFile
{
    use TContent;
    use TSource;
    use TPrimaryKey;

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
        return $this->saveToStorage([[$record->offsetGet($this->getContentKey())]]);
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
        $row = reset($stored);
        if (false === $row || !is_array($row)) {
            throw new MapperException('Cannot load data array from storage');
        }
        $entry = reset($row);
        if (false === $entry) {
            throw new MapperException('Cannot load data entry from storage');
        }
        $record->getEntry($this->getContentKey())->setData($entry, true);
        return true;
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

    /**
     * @param IFileFormat|null $format
     * @throws MapperException
     * @return array<string|int, array<string|int, string|int|float|bool|array<string|int, string|int|float|bool>>>
     */
    abstract protected function loadFromStorage(?IFileFormat $format = null): array;

    /**
     * @param array<string|int, array<string|int, string|int|float|bool|array<string|int, string|int|float|bool>>> $content
     * @param IFileFormat|null $format
     * @throws MapperException
     * @return bool
     */
    abstract protected function saveToStorage(array $content, ?IFileFormat $format = null): bool;
}

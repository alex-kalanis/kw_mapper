<?php

namespace kalanis\kw_mapper\Mappers\File;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\AMapper;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_storage\Storage;
use kalanis\kw_storage\StorageException;


/**
 * Class AKeyValue
 * @package kalanis\kw_mapper\Mappers\Database
 * Key-value pairs somewhere in storage mapped for extra usage
 */
abstract class AKeyValue extends AMapper
{
    use TContent;

    protected $format = '';

    protected $processKey = '';

    public function getAlias(): string
    {
        return $this->getKey();
    }

    public function setKey(string $key): self
    {
        $this->processKey = $key;
        return $this;
    }

    public function getKey(): string
    {
        return $this->processKey;
    }

    protected function setMap(): void
    {
        $this->setPathKey('key');
        $this->setContentKey('content');
    }

    public function setPathKey(string $pathKey): self
    {
        $this->addPrimaryKey($pathKey);
        return $this;
    }

    /**
     * @param ARecord $record
     * @return bool
     * @throws MapperException
     */
    protected function insertRecord(ARecord $record): bool
    {
        return $this->updateRecord($record);
    }

    /**
     * @param ARecord $record
     * @return bool
     * @throws MapperException
     */
    protected function updateRecord(ARecord $record): bool
    {
        $this->setKey($record->offsetGet($this->getPathFromPk($record)));
        return $this->saveToRemoteSource($record->offsetGet($this->getContentKey()));
    }

    /**
     * @param ARecord $record
     * @return int
     * @throws MapperException
     */
    public function countRecord(ARecord $record): int
    {
        $this->setKey($record->offsetGet($this->getPathFromPk($record)));
        return intval(!empty($this->loadFromRemoteSource()));
    }

    /**
     * @param ARecord $record
     * @return bool
     * @throws MapperException
     */
    protected function loadRecord(ARecord $record): bool
    {
        $this->setKey($record->offsetGet($this->getPathFromPk($record)));
        $record->offsetSet($this->getContentKey(), $this->loadFromRemoteSource());
        return true;
    }

    /**
     * @param ARecord $record
     * @return bool
     * @throws MapperException
     */
    protected function deleteRecord(ARecord $record): bool
    {
        $path = $record->offsetGet($this->getPathFromPk($record));
        try {
            if ($this->getStorage()->exists($path)) {
                return $this->getStorage()->remove($path);
            }
            // @codeCoverageIgnoreStart
        } catch (StorageException $ex) {
            return false;
            // @codeCoverageIgnoreEnd
        }
        return true; // not found - operation successful
    }

    public function loadMultiple(ARecord $record): array
    {
        $inPath = $this->getPathFromPk($record);
        $path = $record->offsetGet($inPath);
        $records = [];
        try {
            $contentKeys = $this->getStorage()->lookup($path);
            foreach ($contentKeys as $contentKey) {
                $rec = clone $record;
                $rec->offsetSet($inPath, $contentKey);
                $rec->load();
                $records[] = $rec;
            }
            // @codeCoverageIgnoreStart
        } catch (StorageException $ex) {
            return [];
            // @codeCoverageIgnoreEnd
        }
        return $records;
    }

    /**
     * @param ARecord $record
     * @return string
     * @throws MapperException
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
     * @throws MapperException
     */
    protected function loadFromRemoteSource(): string
    {
        try {
            return $this->getStorage()->read($this->getKey());
        } catch (StorageException $ex) {
            throw new MapperException('Unable to read source', 0, $ex);
        }
    }

    /**
     * @param string $content
     * @return bool
     * @throws MapperException
     */
    protected function saveToRemoteSource(string $content): bool
    {
        try {
            return $this->getStorage()->write($this->getKey(), $content);
        } catch (StorageException $ex) {
            throw new MapperException('Unable to write into source', 0, $ex);
        }
    }

    abstract protected function getStorage(): Storage\Storage;
}

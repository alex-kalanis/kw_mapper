<?php

namespace kalanis\kw_mapper\Mappers;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records;


/**
 * Trait TStore
 * @package kalanis\kw_mapper\Mappers
 * Abstract for manipulation with file content as table
 */
trait TStore
{
    use TTranslate;

    /**
     * @param Records\ARecord $record
     * @throws MapperException
     * @return Records\ARecord[]
     */
    protected function loadSource(Records\ARecord $record): array
    {
        $lines = $this->loadFromStorage();
        $records = [];
        foreach ($lines as &$line) {

            $item = clone $record;

            foreach ($this->getRelations() as $objectKey => $recordKey) {
                $entry = $item->getEntry($objectKey);
                $entry->setData($this->translateTypeFrom($entry->getType(), $line[$recordKey]), true);
            }
            $records[] = $item;
        }
        return $records;
    }

    /**
     * @param Records\ARecord[] $records
     * @throws MapperException
     * @return bool
     */
    protected function saveSource(array $records): bool
    {
        $lines = [];
        foreach ($records as &$record) {
            $dataLine = [];

            foreach ($this->getRelations() as $objectKey => $recordKey) {
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
        foreach ($this->getPrimaryKeys() as $key) {
            $toComplete[] = $record->offsetGet($key);
        }
        return (count(array_filter($toComplete))) ? implode('_', $toComplete) : null ;
    }

    /**
     * @throws MapperException
     * @return array<string|int, string|int|float|array<string|int, string|int|array<string|int, string|int>>>
     */
    abstract protected function loadFromStorage(): array;

    /**
     * @param array<string|int, string|int|float|array<string|int, string|int|array<string|int, string|int>>> $content
     * @throws MapperException
     * @return bool
     */
    abstract protected function saveToStorage(array $content): bool;

    /**
     * @return string[]
     */
    abstract public function getPrimaryKeys(): array;

    /**
     * @return array<string|int, string|int>
     */
    abstract public function getRelations(): array;
}

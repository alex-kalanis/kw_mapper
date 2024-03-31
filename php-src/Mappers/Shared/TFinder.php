<?php

namespace kalanis\kw_mapper\Mappers\Shared;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records;


/**
 * Trait TFinder
 * @package kalanis\kw_mapper\Mappers\Shared
 * Abstract for manipulation with file content as table
 */
trait TFinder
{
    /** @var Records\ARecord[] */
    protected array $records = [];

    /**
     * @param Records\ARecord $record
     * @param bool $usePks
     * @param bool $wantFromStorage
     * @throws MapperException
     * @return Records\ARecord[]
     */
    protected function findMatched(Records\ARecord $record, bool $usePks = false, bool $wantFromStorage = false): array
    {
        $this->loadOnDemand($record);

        $toProcess = array_combine(array_keys($this->records), array_values($this->records)); // copy array - records will be removed when don't match
        if (false === $toProcess) {
            // @codeCoverageIgnoreStart
            // php7-
            throw new MapperException('Combine on field went wrong. Call php support.');
        }
        // @codeCoverageIgnoreEnd
        $toCompare = $this->getArrayToCompare($record, $usePks, $wantFromStorage);

        if ($usePks) { // nothing to get when any necessary primary key is unknown
            foreach ($record->getMapper()->getPrimaryKeys() as $primaryKey) {
                if (!isset($toCompare[$primaryKey])) {
                    return [];
                }
            }
        }

        // through relations
        foreach ($toCompare as $relationKey => $compareValue) {
            foreach ($this->records as $positionKey => $knownRecord) {
                if ( !isset($toProcess[$positionKey]) ) { // not twice
                    continue;
                }
                if ( !$knownRecord->offsetExists($relationKey) ) { // unknown relation key in record is not allowed into compare
                    unset($toProcess[$positionKey]);
                    continue;
                }
                if ( strval($knownRecord->offsetGet($relationKey)) != strval($compareValue) ) {
                    unset($toProcess[$positionKey]);
                    continue;
                }
            }
        }

        return $toProcess;
    }

    /**
     * More records on one mapper - reload with correct one
     * @param Records\ARecord $record
     * @throws MapperException
     */
    protected function loadOnDemand(Records\ARecord $record): void
    {
        if (empty($this->records)) {
            $this->records = $this->loadSource($record);
        } else {
            $test = reset($this->records);
            if (get_class($test) != get_class($record)) { // reload other data - changed record
                // @codeCoverageIgnoreStart
                $this->records = $this->loadSource($record);
            }
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @param Records\ARecord $record
     * @param bool $usePks
     * @param bool $wantFromStorage
     * @throws MapperException
     * @return array<string|int, mixed>
     */
    protected function getArrayToCompare(Records\ARecord $record, bool $usePks, bool $wantFromStorage): array
    {
        $stored = [];
        $written = [];
        foreach ($record as $key => $item) {
            $entry = $record->getEntry($key);
            if ($usePks && !in_array($key, $record->getMapper()->getPrimaryKeys())) {
                continue;
            }
            if (false === $entry->getData() && !$entry->isFromStorage()) {
                continue;
            }
            if ($entry->isFromStorage()) {
                $stored[$key] = $entry->getData();
            } else {
                $written[$key] = $entry->getData();
            }
        }
        return $wantFromStorage ? (empty($stored) ? $written : $stored) : array_merge($stored, $written);
    }

    protected function clearSource(): void
    {
        $this->records = [];
    }

    /**
     * @param Records\ARecord $record
     * @throws MapperException
     * @return Records\ARecord[]
     */
    abstract protected function loadSource(Records\ARecord $record): array;

    /**
     * @return string[]
     */
    abstract public function getPrimaryKeys(): array;

    /**
     * @return array<string|int, string|int>
     */
    abstract public function getRelations(): array;
}

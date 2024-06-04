<?php

namespace kalanis\kw_mapper\Adapters;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;


/**
 * Class DataExchange
 * @package kalanis\kw_mapper\Adapters
 * Simple exchanging data via array
 */
class DataExchange
{
    protected ARecord $record;
    /** @var array<string|int, bool> */
    protected array $excluded = [];

    public function __construct(ARecord $record)
    {
        $this->record = $record;
    }

    /**
     * Add property which will be ignored
     * @param string|int $property
     */
    public function addExclude($property): void
    {
        $this->excluded[$property] = true;
    }

    public function clearExclude(): void
    {
        $this->excluded = [];
    }

    /**
     * Import data into record
     * @param iterable<string|int, mixed> $data
     * @throws MapperException
     * @return int how many entries has been imported
     */
    public function import(iterable $data): int
    {
        $imported = 0;
        foreach ($data as $property => $value) {
            if (!$this->isExcluded($property)
                && $this->record->offsetExists($property)
                && ($this->record->offsetGet($property) != $value)
            ) {
                $this->record->offsetSet($property, $value);
                $imported++;
            }
        }
        return $imported;
    }

    /**
     * Export data from record
     * @return array<string|int, mixed>
     */
    public function export(): array
    {
        $returnData = [];
        foreach ($this->record as $property => $value) {
            if (!$this->isExcluded($property)) {
                $returnData[$property] = $value;
            }
        }
        return $returnData;
    }

    /**
     * @param string|int $property
     * @return bool
     */
    protected function isExcluded($property): bool
    {
        return isset($this->excluded[$property]);
    }

    public function getRecord(): ARecord
    {
        return $this->record;
    }
}

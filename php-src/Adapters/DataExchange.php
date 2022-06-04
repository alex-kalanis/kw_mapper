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
    /** @var ARecord **/
    protected $record;
    /** @var string[] */
    protected $excluded = [];

    public function __construct(ARecord $record)
    {
        $this->record = $record;
    }

    /**
     * Add property which will be ignored
     * @param string $property
     */
    public function addExclude(string $property): void
    {
        $this->excluded[$property] = true;
    }

    public function clearExclude(): void
    {
        $this->excluded = [];
    }

    /**
     * Import data into record
     * @param iterable $data
     * @return int how much nas been imported
     * @throws MapperException
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
     * @return array
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

    protected function isExcluded(string $property): bool
    {
        return isset($this->excluded[$property]);
    }
}

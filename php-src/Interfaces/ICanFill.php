<?php

namespace kalanis\kw_mapper\Interfaces;


/**
 * Interface ICanFill
 * @package kalanis\kw_mapper\Interfaces
 * Can fill data from source
 * Class implementing this interface will be used as representation of corresponding entry.
 *
 * Example in terms of code:

class RecordWithObject extends Records\ASimpleRecord
{
    protected function addEntries(): void
    {
        // ...
        $this->addEntry('iCanFillClass', IEntryType::TYPE_OBJECT, ICanFillInstance::class);
        // ...
    }
}

// ...

class ICanFillInstance implements ICanFill
{
    // ...
}

 */
interface ICanFill
{
    /**
     * Fill class with passed data from storage
     * @param mixed $data
     */
    public function fillData($data): void;

    /**
     * Getting data from class implementing this interface and put them into storage
     * @return mixed
     */
    public function dumpData();
}

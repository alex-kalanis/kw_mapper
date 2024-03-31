<?php

namespace kalanis\kw_mapper\Adapters;


use kalanis\kw_mapper\Interfaces\ICanFill;
use stdClass;


/**
 * Class MappedStdClass
 * @package kalanis\kw_mapper\Adapters
 * Simple entry to fill - based on stdClass
 */
class MappedStdClass extends stdClass implements ICanFill
{
    private const SIMPLE = '__simple';

    public function fillData($data): void
    {
        if (is_iterable($data)) {
            if (isset($this->{self::SIMPLE})) {
                unset($this->{self::SIMPLE});
            }
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        } else {
            $this->{self::SIMPLE} = $data;
        }
    }

    public function dumpData()
    {
        if (isset($this->{self::SIMPLE})) {
            return $this->{self::SIMPLE};
        }
        $result = [];
        // @phpstan-ignore-next-line
        foreach ($this as $key => $value) { // traversable over stdClass
            $result[$key] = $value;
        }
        return $result;
    }
}

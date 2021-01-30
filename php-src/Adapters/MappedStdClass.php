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
    const SIMPLE = 'simple';

    public function fillData($data): void
    {
        if (is_iterable($data)) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
            unset($this->{self::SIMPLE});
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
        foreach ($this as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }
}

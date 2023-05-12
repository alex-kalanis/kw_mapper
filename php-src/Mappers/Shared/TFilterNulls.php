<?php

namespace kalanis\kw_mapper\Mappers\Shared;


/**
 * Trait TFilterNulls
 * @package kalanis\kw_mapper\Mappers\Shared
 */
trait TFilterNulls
{
    /**
     * When it contains null value, then it's empty for usage - because Null has direct representation in query builder as operation -> no need to pass data
     * @param mixed $value
     * @return bool
     */
    public function filterNullValues($value): bool
    {
        return !is_null($value);
    }
}

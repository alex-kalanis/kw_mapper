<?php

namespace kalanis\kw_mapper\Mappers\Shared;


use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Records\Entry;


/**
 * Trait TEntityChanged
 * @package kalanis\kw_mapper\Mappers\Shared
 * Check if value has been changed and will be changed
 */
trait TEntityChanged
{
    /**
     * @param Entry $entry
     * @return bool
     */
    protected function ifEntryChanged(Entry $entry): bool
    {
        $toCompare = $entry->getData();
        return (IEntryType::TYPE_BOOLEAN == $entry->getType())
            ? !is_null($toCompare)
            : (false !== $toCompare)
        ;
    }
}

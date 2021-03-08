<?php

namespace kalanis\kw_mapper\Records;


use kalanis\kw_mapper\Interfaces\IEntryType;


/**
 * trait TFill
 * @package kalanis\kw_mapper\Records
 * Easier way to fill entries
 */
trait TFill
{
    protected function typedFill(Entry &$entry, $value): void
    {
        $entry->setData($this->typedFillSelection($entry, $value));
    }

    protected function typedFillSelection(Entry &$entry, $dbValue)
    {
        switch ($entry->getType()) {
            case IEntryType::TYPE_BOOLEAN:
                return boolval(intval(strval($dbValue)));
            case IEntryType::TYPE_INTEGER:
                return intval(strval($dbValue));
            case IEntryType::TYPE_FLOAT:
                return floatval(strval($dbValue));
            case IEntryType::TYPE_STRING:
                return strval($dbValue);
            case IEntryType::TYPE_SET:
            case IEntryType::TYPE_ARRAY:
            case IEntryType::TYPE_OBJECT:
            default:
                return $dbValue;
        }
    }
}

<?php

namespace kalanis\kw_mapper\Records;


use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;


/**
 * Class ASimpleRecord
 * @package kalanis\kw_mapper\Records
 * Class to map entries to their respective values - loose typing
 */
abstract class ASimpleRecord extends ARecord
{
    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws MapperException
     */
    final public function offsetSet($offset, $value)
    {
        $this->offsetCheck($offset);
        $data = & $this->entries[$offset];
        switch ($data->getType()) {
            case IEntryType::TYPE_BOOLEAN:
            case IEntryType::TYPE_INTEGER:
            case IEntryType::TYPE_FLOAT:
            case IEntryType::TYPE_STRING:
            case IEntryType::TYPE_SET:
            case IEntryType::TYPE_ARRAY:
                $data->setData($value);
                return;
            case IEntryType::TYPE_OBJECT:
                $this->reloadClass($data);
                $class = $data->getData();
                $class->fillData($value);
                return; // fill data elsewhere
            default:
                // @codeCoverageIgnoreStart
                // happens only when someone is evil enough and change type directly on entry
                throw new MapperException(sprintf('Unknown type *%d*', $data->getType()));
                // @codeCoverageIgnoreEnd
        }
    }
}

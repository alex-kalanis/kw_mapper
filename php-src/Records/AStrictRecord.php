<?php

namespace kalanis\kw_mapper\Records;


use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;


/**
 * Class AStrictRecord
 * @package kalanis\kw_mapper\Records
 * Class to map entries to their respective values - strict typing
 * The level of "obstruction" to accessing properties is necessary
 * or it could not be possible to guarantee content values.
 */
abstract class AStrictRecord extends ARecord
{
    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws MapperException
     */
    final public function offsetSet($offset, $value): void
    {
        $this->offsetCheck($offset);
        $data = & $this->entries[$offset];
        switch ($data->getType()) {
            case IEntryType::TYPE_BOOLEAN:
                $this->checkBool($value, $offset);
                break;
            case IEntryType::TYPE_INTEGER:
            case IEntryType::TYPE_FLOAT:
                $this->checkNumeric($value, $offset);
                $this->checkSize($value, floatval($data->getParams()));
                break;
            case IEntryType::TYPE_STRING:
                $this->checkString($value, $offset);
                $this->checkLength($value, intval($data->getParams()));
                break;
            case IEntryType::TYPE_SET:
                $this->checkPreset($value, $data->getParams());
                break;
            case IEntryType::TYPE_ARRAY:
                $this->checkArrayForNotEntries($value, $offset);
                break;
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
        $data->setData($value);
    }

    /**
     * @param mixed $value
     * @param string $key
     * @throws MapperException
     */
    private function checkBool($value, string $key)
    {
        if (is_null($value)) {
            return;
        }
        if (!is_bool($value)) {
            throw new MapperException(sprintf('Try to set something other than number into key *%s*', $key));
        }
    }

    /**
     * @param mixed $value
     * @param string $key
     * @throws MapperException
     */
    private function checkNumeric($value, string $key)
    {
        if (is_null($value)) {
            return;
        }
        if (!is_numeric($value)) {
            throw new MapperException(sprintf('Try to set something other than number into key *%s*', $key));
        }
    }

    /**
     * @param mixed $value
     * @param string $key
     * @throws MapperException
     */
    private function checkString($value, string $key)
    {
        if (is_null($value)) {
            return;
        }
        if (!is_string($value)) {
            throw new MapperException(sprintf('Try to set something other than string into key *%s*', $key));
        }
    }

    /**
     * @param mixed $value
     * @param float $limit
     * @throws MapperException
     */
    private function checkSize($value, float $limit)
    {
        if (is_null($value)) {
            return;
        }
        if ($value > $limit) {
            throw new MapperException(sprintf('Try to set number larger than allowed size (*%.4f* > *%.4f*)', $value, $limit));
        }
    }

    /**
     * @param mixed $value
     * @param int $limit
     * @throws MapperException
     */
    private function checkLength($value, int $limit)
    {
        if (is_null($value)) {
            return;
        }
        $size = mb_strlen($value);
        if ($size > $limit) {
            throw new MapperException(sprintf('Try to set string longer than allowed size (*%d* > *%d*)', $size, $limit));
        }
    }

    /**
     * @param mixed $value
     * @param array $preset
     * @throws MapperException
     */
    private function checkPreset($value, $preset)
    {
        if (is_null($value)) {
            return;
        }
        if (!in_array($value, $preset)) {
            throw new MapperException(sprintf('Try to set *%s* that is not in preset values', $value));
        }
    }

    /**
     * @param mixed $value
     * @param string $key
     * @throws MapperException
     */
    private function checkArrayForNotEntries($value, string $key)
    {
        if (!is_array($value)) {
            throw new MapperException(sprintf('You must set array into key *%s*', $key));
        }
        foreach ($value as $item) {
            if (!$item instanceof ARecord) {
                throw new MapperException(sprintf('Array in key *%s* contains something that is not link to another mapper', $key));
            }
        }
    }
}

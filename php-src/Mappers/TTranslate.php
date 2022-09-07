<?php

namespace kalanis\kw_mapper\Mappers;


use kalanis\kw_mapper\Interfaces\IEntryType;


/**
 * Trait TTranslate
 * @package kalanis\kw_mapper\Mappers
 */
trait TTranslate
{
    /**
     * @param int $type
     * @param mixed $input
     * @return bool|float|int|mixed|string
     */
    protected function translateTypeFrom(int $type, $input)
    {
        switch ($type) {
            case IEntryType::TYPE_BOOLEAN:
                return boolval(intval($input));
            case IEntryType::TYPE_INTEGER:
                return intval($input);
            case IEntryType::TYPE_FLOAT:
                return floatval($input);
            case IEntryType::TYPE_ARRAY:
            case IEntryType::TYPE_OBJECT:
                return unserialize($input);
            default:
                return strval($input);
        }
    }

    /**
     * @param int $type
     * @param mixed $input
     * @return string
     */
    protected function translateTypeTo(int $type, $input): string
    {
        switch ($type) {
            case IEntryType::TYPE_BOOLEAN:
                return strval(intval($input));
            case IEntryType::TYPE_ARRAY:
            case IEntryType::TYPE_OBJECT:
                return serialize($input);
            default:
                return strval($input);
        }
    }
}

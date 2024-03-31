<?php

namespace kalanis\kw_mapper\Interfaces;


/**
 * Interface IEntryType
 * @package kalanis\kw_mapper\Interfaces
 * Types of entries which are accessible from records
 */
interface IEntryType
{
    public const TYPE_BOOLEAN = 1; // elementary content - boolean
    public const TYPE_INTEGER = 2; // basic content - integer
    public const TYPE_FLOAT = 3; // basic content - float
    public const TYPE_STRING = 4; // a bit complicated - string
    public const TYPE_ARRAY = 5; // simple array of entries
    public const TYPE_SET = 6; // a really complicated - preset values
    public const TYPE_OBJECT = 7; // complex object which usually needs external instance and has ICanFill interface
}

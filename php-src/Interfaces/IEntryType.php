<?php

namespace kalanis\kw_mapper\Interfaces;


/**
 * Interface IEntryType
 * @package kalanis\kw_mapper\Interfaces
 * Types of entries which are accessible from records
 */
interface IEntryType
{
    const TYPE_BOOLEAN = 1; // elementary content - boolean
    const TYPE_INTEGER = 2; // basic content - integer
    const TYPE_FLOAT = 3; // basic content - float
    const TYPE_STRING = 4; // a bit complicated - string
    const TYPE_ARRAY = 5; // simple array of entries
    const TYPE_SET = 6; // a really complicated - preset values
    const TYPE_OBJECT = 7; // complex object which usually needs external instance and has ICanFill interface
}

<?php

namespace kalanis\kw_mapper\Interfaces;


/**
 * Interface IType
 * @package kalanis\kw_mapper\Interfaces
 * Types of entries which are accessable from records
 */
interface IType
{
    const TYPE_BOOLEAN = 1; // elementary content - boolean
    const TYPE_INTEGER = 2; // basic content - integer
    const TYPE_STRING = 3; // a bit complicated - string
    const TYPE_ARRAY = 4; // simple array of entries
    const TYPE_OBJECT = 5; // complex object which usually needs external instance and has ICanFill interface
}

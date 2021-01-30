<?php

namespace kalanis\kw_mapper\Interfaces;


/**
 * Interface ICanFill
 * @package kalanis\kw_mapper\Interfaces
 * Can fill data from source
 */
interface ICanFill
{
    public function fillData($data): void;

    public function dumpData();
}

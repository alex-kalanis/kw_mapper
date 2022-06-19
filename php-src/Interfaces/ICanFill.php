<?php

namespace kalanis\kw_mapper\Interfaces;


/**
 * Interface ICanFill
 * @package kalanis\kw_mapper\Interfaces
 * Can fill data from source
 */
interface ICanFill
{
    /**
     * @param mixed $data
     */
    public function fillData($data): void;

    /**
     * @return mixed
     */
    public function dumpData();
}

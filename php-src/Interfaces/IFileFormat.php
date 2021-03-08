<?php

namespace kalanis\kw_mapper\Interfaces;


use kalanis\kw_mapper\MapperException;


/**
 * Interface IFileFormat
 * @package kalanis\kw_mapper\Interfaces
 * How the content will be formatted into/from file
 */
interface IFileFormat
{
    /**
     * @param string $content
     * @return array
     * @throws MapperException
     */
    public function unpack(string $content): array;

    /**
     * @param array $content
     * @return string
     * @throws MapperException
     */
    public function pack(array $content): string;
}

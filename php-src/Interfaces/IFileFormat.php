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
     * @throws MapperException
     * @return array<string|int, array<string|int, string|int|float|bool|array<string|int, string|int|float|bool>>>
     */
    public function unpack(string $content): array;

    /**
     * @param array<string|int, array<string|int, string|int|float|bool|array<string|int, string|int|float|bool>>> $content
     * @throws MapperException
     * @return string
     */
    public function pack(array $content): string;
}

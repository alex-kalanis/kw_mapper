<?php

namespace kalanis\kw_mapper\Storage\Shared\FormatFiles;


use kalanis\kw_mapper\Interfaces\IFileFormat;
use kalanis\kw_mapper\MapperException;


/**
 * Class Json
 * @package kalanis\kw_mapper\Storage\Shared\FormatFiles
 */
class Json implements IFileFormat
{
    public function unpack(string $content): array
    {
        $result = @json_decode($content, true);
        if (is_null($result) && json_last_error()) {
            throw new MapperException('Cannot parse JSON input - ' . json_last_error_msg());
        }
        return $result;
    }

    public function pack(array $records): string
    {
        $result = json_encode($records);
        if (false === $result && json_last_error()) {
            // @codeCoverageIgnoreStart
            throw new MapperException('Cannot parse JSON output - ' . json_last_error_msg());
        }
        // @codeCoverageIgnoreEnd
        return strval($result);
    }
}

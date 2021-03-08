<?php

namespace kalanis\kw_mapper\Storage\File\Formats;


use kalanis\kw_mapper\Interfaces\IFileFormat;
use kalanis\kw_mapper\MapperException;


/**
 * Class Yaml
 * @package kalanis\kw_mapper\Storage\File\Formats
 */
class Yaml implements IFileFormat
{
    public function unpack(string $content): array
    {
        $result = yaml_parse($content);
        if (false === $result) {
            throw new MapperException('Cannot parse YAML input');
        }
        return $result;
    }

    public function pack(array $records): string
    {
        return yaml_emit($records);
    }
}

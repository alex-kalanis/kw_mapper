<?php

namespace kalanis\kw_mapper\Storage\Shared\FormatFiles;


use kalanis\kw_mapper\Interfaces\IFileFormat;
use kalanis\kw_mapper\MapperException;


/**
 * Class SinglePage
 * @package kalanis\kw_mapper\Storage\Shared\FormatFiles
 */
class SinglePage implements IFileFormat
{
    public function unpack(string $content): array
    {
        return [[$content]];
    }

    public function pack(array $records): string
    {
        $line = reset($records);
        if (false !== $line && is_array($line)) {
            $content = reset($line);
            if (false !== $content) {
                return strval($content);
            }
        }
        throw new MapperException('Cannot pack single page into data stream');
    }
}

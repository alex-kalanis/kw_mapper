<?php

namespace kalanis\kw_mapper\Storage\File\Formats;


use kalanis\kw_mapper\Interfaces\IFileFormat;


/**
 * Class SinglePage
 * @package kalanis\kw_mapper\Storage\File\Formats
 */
class SinglePage implements IFileFormat
{
    public function unpack(string $content): array
    {
        return [$content];
    }

    public function pack(array $records): string
    {
        return strval(reset($records));
    }
}

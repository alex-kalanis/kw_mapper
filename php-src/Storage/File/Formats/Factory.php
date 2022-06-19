<?php

namespace kalanis\kw_mapper\Storage\File\Formats;


use kalanis\kw_mapper\Interfaces\IFileFormat;
use kalanis\kw_mapper\MapperException;


/**
 * Class Factory
 * @package kalanis\kw_mapper\Storage\File\Formats
 */
class Factory
{
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * @param string $path
     * @throws MapperException
     * @return IFileFormat
     */
    public function getFormatClass(string $path): IFileFormat
    {
        if (!class_exists($path)) {
            throw new MapperException(sprintf('Wanted class *%s* not exists!', $path));
        }
        $instance = new $path();
        if (!$instance instanceof IFileFormat) {
            throw new MapperException(sprintf('Defined class *%s* is not instance of IFilePacker!', $path));
        }
        return $instance;
    }
}

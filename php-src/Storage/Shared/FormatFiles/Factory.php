<?php

namespace kalanis\kw_mapper\Storage\Shared\FormatFiles;


use kalanis\kw_mapper\Interfaces\IFileFormat;
use kalanis\kw_mapper\MapperException;
use ReflectionClass;
use ReflectionException;


/**
 * Class Factory
 * @package kalanis\kw_mapper\Storage\Shared\FormatFiles
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
        try {
            /** @var class-string $path */
            $reflect = new ReflectionClass($path);
            $instance = $reflect->newInstance();
        } catch (ReflectionException $ex) {
            throw new MapperException(sprintf('Wanted class *%s* not exists!', $path), $ex->getCode(), $ex);
        }
        if (!$instance instanceof IFileFormat) {
            throw new MapperException(sprintf('Defined class *%s* is not instance of IFileFormat!', $path));
        }
        return $instance;
    }
}

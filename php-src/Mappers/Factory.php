<?php

namespace kalanis\kw_mapper\Mappers;


use kalanis\kw_mapper\MapperException;


/**
 * Class Factory
 * @package kalanis\kw_mapper\Mappers
 * Factory for getting available mappers
 */
class Factory
{
    protected static $instances = [];

    /**
     * Which instances of mappers are available
     * @param string $path
     * @return AMapper
     * @throws MapperException when initialization fails
     */
    public function getInstance(string $path): AMapper
    {
        if (!isset(static::$instances[$path])) {
            if (!class_exists($path)) {
                throw new MapperException(sprintf('Wanted class *%s* not exists!', $path));
            }
            $instance = new $path();
            if (!$instance instanceof AMapper) {
                throw new MapperException(sprintf('Defined class *%s* is not instance of AMapper!', $path));
            }
            static::$instances[$path] = $instance;
        }
        return static::$instances[$path];
    }
}

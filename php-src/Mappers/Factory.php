<?php

namespace kalanis\kw_mapper\Mappers;


use kalanis\kw_mapper\MapperException;
use ReflectionClass;
use ReflectionException;


/**
 * Class Factory
 * @package kalanis\kw_mapper\Mappers
 * Factory for getting available mappers
 */
class Factory
{
    /** @var array<string, AMapper> */
    protected static $instances = [];

    /**
     * Which instances of mappers are available
     * @param string $path
     * @throws MapperException when initialization fails
     * @return AMapper
     */
    public function getInstance(string $path): AMapper
    {
        if (!isset(static::$instances[$path])) {
            try {
                /** @var class-string $path */
                $reflex = new ReflectionClass($path);
                $instance = $reflex->newInstance();
            } catch (ReflectionException $ex) {
                throw new MapperException(sprintf('Wanted class *%s* does not exists!', $path), $ex->getCode(), $ex);
            }
            if (!$instance instanceof AMapper) {
                throw new MapperException(sprintf('Defined class *%s* is not instance of AMapper!', $path));
            }
            static::$instances[$path] = $instance;
        }
        return static::$instances[$path];
    }
}

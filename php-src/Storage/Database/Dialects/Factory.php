<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\MapperException;


/**
 * Class Factory
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 */
class Factory
{
    protected static $instances = [];

    public static function getInstance(): self
    {
        return new static();
    }

    /**
     * @param string $path
     * @return ADialect
     * @throws MapperException
     */
    public function getDialectClass(string $path): ADialect
    {
        if (!isset(static::$instances[$path])) {
            if (!class_exists($path)) {
                throw new MapperException(sprintf('Wanted class %s not exists!', $path));
            }
            $instance = new $path();
            if (!$instance instanceof ADialect) {
                throw new MapperException(sprintf('Defined class %s is not instance of AMapper!', $path));
            }
            static::$instances[$path] = $instance;
        }
        return static::$instances[$path];
    }
}

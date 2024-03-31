<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\MapperException;
use ReflectionClass;
use ReflectionException;


/**
 * Class Factory
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 */
class Factory
{
    /** @var array<string, ADialect> */
    protected static array $instances = [];

    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * @param string $path
     * @throws MapperException
     * @return ADialect
     */
    public function getDialectClass(string $path): ADialect
    {
        if (!isset(static::$instances[$path])) {
            try {
                /** @var class-string $path */
                $reflect = new ReflectionClass($path);
                $instance = $reflect->newInstance();
            } catch (ReflectionException $ex) {
                throw new MapperException(sprintf('Wanted class *%s* not exists!', $path), $ex->getCode(), $ex);
            }
            if (!$instance instanceof ADialect) {
                throw new MapperException(sprintf('Defined class *%s* is not instance of AMapper!', $path));
            }
            static::$instances[$path] = $instance;
        }
        return static::$instances[$path];
    }
}

<?php

namespace kalanis\kw_mapper\Storage\Shared;


use kalanis\kw_mapper\MapperException;


/**
 * trait TCheckExt
 * @package kalanis\kw_mapper\Storage\Shared
 * @codeCoverageIgnore better when not run!
 */
trait TCheckExt
{
    /**
     * @param string $name
     * @throws MapperException
     */
    public function checkExtension(string $name): void
    {
        if (!extension_loaded($name)) {
            throw new MapperException(sprintf('Extension *%s* is not present!', $name));
        }
    }
}

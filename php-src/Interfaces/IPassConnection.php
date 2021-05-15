<?php

namespace kalanis\kw_mapper\Interfaces;


use kalanis\kw_mapper\MapperException;


/**
 * Interface IPassConnection
 * @package kalanis\kw_mapper\Interfaces
 * Can pass connection to server used as storage
 */
interface IPassConnection
{
    /**
     * Open connection
     * @throws MapperException
     */
    public function connect(): void;

    /**
     * Close connection
     * @throws MapperException
     */
    public function disconnect(): void;

    /**
     * Returns object/resource when connected or null when not
     * @return object|resource|null
     */
    public function getConnection();
}

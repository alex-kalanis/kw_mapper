<?php

namespace kalanis\kw_mapper\Storage\Database;


use kalanis\kw_mapper\MapperException;


/**
 * trait TConnection
 * @package kalanis\kw_mapper\Storage\Database
 */
trait TConnection
{
    /** @var object|resource|null */
    protected $connection = null;

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Close connection
     */
    public function disconnect(): void
    {
        if ($this->isConnected()) {
            $this->connection = null;
        }
    }

    /**
     * Reset connection
     * @throws MapperException
     */
    public function reconnect(): void
    {
        $this->disconnect();
        $this->connect();
    }

    /**
     * Open connection
     * @throws MapperException
     */
    abstract public function connect(): void;

    public function isConnected(): bool
    {
        return !empty($this->connection);
    }

    /**
     * @return object|resource|null
     */
    public function getConnection()
    {
        return $this->connection;
    }
}

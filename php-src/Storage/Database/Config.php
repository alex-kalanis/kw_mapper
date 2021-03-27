<?php

namespace kalanis\kw_mapper\Storage\Database;


/**
 * Class Config
 * @package kalanis\kw_mapper\Storage\Database
 * Settings for accessing database
 * Not everything will be used by each database
 */
class Config
{
    protected $driver = ''; // determine which driver for database will be used; they are defined in IDriverSources
    protected $sourceName = ''; // determine source identifier, must be unique in whole connection system
    protected $location = ''; // location, host
    protected $port = 0; // port number, usually something you have preset
    protected $user = ''; // which user will access data
    protected $password = ''; // his password
    protected $database = ''; // which database will be processed
    protected $timeout = 8600; // how long it will be connected
    protected $persistent = false; // it will try to live longer
    protected $type = null; // special - type for connection

    public static function init()
    {
        return new static();
    }

    public function setTarget(string $driver, string $sourceName, string $location, int $port, ?string $user, ?string $password, string $database): self
    {
        $this->driver = $driver;
        $this->sourceName = $sourceName;
        $this->location = $location;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        return $this;
    }

    public function setParams(int $timeout = 8600, bool $persistent = false, $type = null): self
    {
        $this->timeout = $timeout;
        $this->persistent = $persistent;
        $this->type = $type;
        return $this;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getSourceName(): string
    {
        return $this->sourceName;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function isPersistent(): bool
    {
        return $this->persistent;
    }

    public function getType()
    {
        return $this->type;
    }
}

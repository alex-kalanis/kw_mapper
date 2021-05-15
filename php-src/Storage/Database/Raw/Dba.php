<?php

namespace kalanis\kw_mapper\Storage\Database\Raw;


use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\Interfaces\IPassConnection;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\ADatabase;
use kalanis\kw_mapper\Storage\Database\TConnection;


/**
 * Class Dba
 * @package kalanis\kw_mapper\Storage\Database\Raw
 * @link https://www.php.net/manual/en/book.dba.php
 * @codeCoverageIgnore remote connection
 */
class Dba extends ADatabase implements IPassConnection
{
    use TConnection;

    protected $extension = 'dba';
    /** @var resource|null */
    protected $connection = null;

    public function languageDialect(): string
    {
        return '\kalanis\kw_mapper\Storage\Database\Dialects\EmptyDialect';
    }

    public function disconnect(): void
    {
        if ($this->isConnected()) {
            dba_close($this->connection);
            $this->connection = null;
        }
    }

    /**
     * @param string $key
     * @return string[]
     * @throws MapperException
     */
    public function query(string $key): array
    {
        if (empty($key)) {
            return [];
        }

        $this->connect();

        $results = [];
        $key = dba_firstkey($this->connection);

        while (!is_null($key)) {
            $results[] = dba_fetch($key, $this->connection);
            $key = dba_nextkey($this->connection);
        }

        return $results;
    }

    /**
     * @param string $key
     * @param string $action
     * @param string $content
     * @return bool
     * @throws MapperException
     */
    public function exec(string $action, string $key, string $content = ''): bool
    {
        if (empty($key)) {
            return false;
        }

        $this->connect();

        if (dba_exists($key, $this->connection)) {
            if (IDriverSources::ACTION_UPDATE == $action) {
                return dba_replace($key, $content, $this->connection);
            } elseif (IDriverSources::ACTION_DELETE == $action) {
                return dba_delete($key, $this->connection);
            } else {
                return false;
            }
        } else {
            if (IDriverSources::ACTION_UPDATE == $action) {
                return dba_insert($key, $content, $this->connection);
            } elseif (IDriverSources::ACTION_INSERT == $action) {
                return dba_insert($key, $content, $this->connection);
            } else {
                return false;
            }
        }
    }

    /**
     * @throws MapperException
     */
    public function connect(): void
    {
        if (!$this->isConnected()) {
            $this->connection = $this->connectToServer();
        }
    }

    /**
     * @return resource
     * @throws MapperException
     */
    protected function connectToServer()
    {
        $connection = dba_open(
            $this->config->getLocation(),
            $this->config->getDatabase(),
            $this->config->getType()
        );
        if (!$connection) {
            throw new MapperException('DBA connection failed.');
        }

        return $connection;
    }
}

<?php

namespace kalanis\kw_mapper\Storage\Database\Raw;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\ADatabase;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;
use MongoDB\Driver;


/**
 * Class MongoDb
 * @package kalanis\kw_mapper\Storage\Database\Raw
 * Connector to MongoDB
 * @link https://docs.mongodb.com/drivers/php/
 * @link https://www.php.net/manual/en/class.mongodb.php
 * @link https://www.tutorialspoint.com/mongodb/mongodb_overview.htm
 * @codeCoverageIgnore remote connection
 */
class MongoDb extends ADatabase
{
    protected $extension = 'mongodb';
    /** @var Driver\Manager|null */
    protected $connection = null;

    public function languageDialect(): string
    {
        return '\kalanis\kw_mapper\Storage\Database\Dialects\MongoDb';
    }

    public function reconnect(): void
    {
        $this->connection = null;
    }

    /**
     * @param QueryBuilder $builder
     * @param Driver\Query $query
     * @return Driver\Cursor
     * @throws MapperException
     */
    public function query(QueryBuilder $builder, Driver\Query $query)
    {
        if (!$this->isConnected()) {
            $this->connection = $this->connectToServer();
        }

        try {
            return $this->connection->executeQuery($this->config->getDatabase() . '.' . $builder->getBaseTable(), $query);
        } catch (Driver\Exception\Exception $ex) {
            throw new MapperException('Mongo query failed.', 0, $ex);
        }
    }

    public function exec(QueryBuilder $builder, Driver\BulkWrite $write): bool
    {
        if (!$this->isConnected()) {
            $this->connection = $this->connectToServer();
        }

        $result = $this->connection->executeBulkWrite($this->config->getDatabase() . '.' . $builder->getBaseTable(), $write);
        return $result->isAcknowledged();
    }

    protected function connectToServer(): Driver\Manager
    {
        $connection = new Driver\Manager(sprintf('mongodb://%s%s@%s%s/%s',
            ($this->config->getUser()) ?: '',
            !empty($this->config->getPassword()) ? ':'. $this->config->getPassword() : '',
            $this->config->getLocation(),
            !empty($this->config->getPort()) ? ':'. $this->config->getPort() : '',
            $this->config->getDatabase()
        ), $this->attributes);

        return $connection;
    }

    public function isConnected(): bool
    {
        return !empty($this->connection);
    }
}

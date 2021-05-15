<?php

namespace kalanis\kw_mapper\Storage\Database\Raw;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\ASQL;
use kalanis\kw_mapper\Storage\Database\TBindNames;


/**
 * Class MySQLi
 * @package kalanis\kw_mapper\Storage\Database\Raw
 * Problematic connector to MySQL just for compatibility - USE PDO instead!!!
 * @codeCoverageIgnore remote connection
 */
class MySQLi extends ASQL
{
    use TBindNames;

    protected $extension = 'mysqli';
    /** @var \mysqli|null */
    protected $connection = null;
    /** @var \mysqli_stmt|null */
    protected $lastStatement;

    public function disconnect()
    {
        if ($this->isConnected()) {
            $this->connection->close();
        }
        $this->connection = null;
    }

    public function languageDialect(): string
    {
        return '\kalanis\kw_mapper\Storage\Database\Dialects\MySQL';
    }

    public function query(string $query, array $params, int $fetchType = MYSQLI_ASSOC): array
    {
        if (empty($query)) {
            return [];
        }

        $this->connect();

        $statement = $this->connection->stmt_init();
        list($updQuery, $binds, $types) = $this->bindFromNamedToQuestions($query, $params);
        $statement->prepare($updQuery);
        if (!empty($binds)) {
            $statement->bind_param(str_repeat('s', count($binds)), ...$binds);
        }
        $statement->execute();
        $result = $statement->get_result();

        $this->lastStatement = $statement;

        return $result->fetch_all($fetchType);
    }

    public function exec(string $query, array $params): bool
    {
        if (empty($query)) {
            return false;
        }

        $this->connect();

        $statement = $this->connection->stmt_init();
        list($updQuery, $binds, $types) = $this->bindFromNamedToQuestions($query, $params);
        $statement->prepare($updQuery);
        if (!empty($binds)) {
            $statement->bind_param(implode('', $types), ...$binds);
        }
        $this->lastStatement = $statement;

        return $statement->execute();
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
     * @return \mysqli
     * @throws MapperException
     */
    protected function connectToServer(): \mysqli
    {
        $connection = new \mysqli(
            $this->config->getLocation(),
            $this->config->getUser(),
            $this->config->getPassword(),
            $this->config->getDatabase(),
            $this->config->getPort()
        );
        if ($connection->connect_errno) {
            throw new MapperException('mysqli connection error: ' . $connection->connect_error);
        }

//        foreach ($this->attributes as $key => $value){
//            $connection->setAttribute($key, $value);
//        }

        $connection->set_charset('utf8');
        if ($connection->errno) {
            throw new MapperException('mysqli error: ' . $connection->error);
        }
        $connection->query('SET NAMES utf8;');

        return $connection;
    }

    public function lastInsertId(): ?string
    {
        return $this->lastStatement ? $this->lastStatement->insert_id : null ;
    }

    public function rowCount(): ?int
    {
        return $this->lastStatement ? $this->lastStatement->num_rows : null ;
    }

    public function beginTransaction(): bool
    {
        if (!$this->isConnected()) {
            $this->connection = $this->connectToServer();
        }

        return (bool)$this->connection->begin_transaction();
    }

    public function commit(): bool
    {
        return (bool)$this->connection->commit();
    }

    public function rollBack(): bool
    {
        return (bool)$this->connection->rollBack();
    }
}

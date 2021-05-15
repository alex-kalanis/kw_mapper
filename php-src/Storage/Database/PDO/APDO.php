<?php

namespace kalanis\kw_mapper\Storage\Database\PDO;


use kalanis\kw_mapper\Storage\Database\ASQL;
use PDO;
use PDOStatement;


/**
 * Class APDO
 * @package kalanis\kw_mapper\Storage\Database\PDO
 * PHP data object abstraction
 * Uses placeholders, not question marks
 * @codeCoverageIgnore remote connection
 */
abstract class APDO extends ASQL
{
    /** @var PDO|null */
    protected $connection = null;
    /** @var PDOStatement|null */
    protected $lastStatement;

    public function query(string $query, array $params, int $fetchType = PDO::FETCH_ASSOC): array
    {
        if (empty($query)) {
            return [];
        }

        $this->connect();

        $statement = $this->connection->prepare($query);
        foreach ($params as $key => $param) {
            $statement->bindParam($key, $param);
        }
        $statement->execute();

        $this->lastStatement = $statement;

        return $statement->fetchAll($fetchType);
    }

    public function exec(string $query, array $params): bool
    {
        if (empty($query)) {
            return false;
        }

        $this->connect();

        $statement = $this->connection->prepare($query);
        foreach ($params as $key => $param) {
            $statement->bindParam($key, $param);
        }
        $statement->execute();

        $this->lastStatement = $statement;

        return $statement->closeCursor();
    }

    public function connect(): void
    {
        if (!$this->isConnected()) {
            $this->connection = $this->connectToServer();
        }
    }

    abstract protected function connectToServer(): PDO;

    public function lastInsertId(): ?string
    {
        return $this->connection->lastInsertId();
    }

    public function rowCount(): ?int
    {
        return $this->lastStatement ? $this->lastStatement->rowCount() : null ;
    }

    public function beginTransaction(): bool
    {
        // @codeCoverageIgnoreStart
        if (!$this->isConnected()) {
            $this->connection = $this->connectToServer();
        }
        // @codeCoverageIgnoreEnd

        return (bool)$this->connection->beginTransaction();
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

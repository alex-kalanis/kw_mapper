<?php

namespace kalanis\kw_mapper\Storage\Database\PDO;


use kalanis\kw_mapper\Storage\Database\ADatabase;
use PDO;
use PDOStatement;


/**
 * Class APDO
 * @package kalanis\kw_mapper\Storage\Database
 * PHP data object abstraction
 * Uses placeholders, not question marks
 */
abstract class APDO extends ADatabase
{
    /** @var PDO|null */
    protected $connection = null;
    /** @var PDOStatement|null */
    protected $lastStatement;

    public function reconnect(): void
    {
        $this->connection = null;
    }

    public function query(string $query, array $params, int $fetchType = PDO::FETCH_ASSOC): array
    {
        if (empty($query)) {
            return [];
        }

        if (!$this->isConnected()) {
            $this->connection = $this->connectToServer();
        }

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

        if (!$this->isConnected()) {
            $this->connection = $this->connectToServer();
        }

        $statement = $this->connection->prepare($query);
        foreach ($params as $key => $param) {
            $statement->bindParam($key, $param);
        }
        $statement->execute();

        $this->lastStatement = $statement;

        return $statement->closeCursor();
    }

    abstract protected function connectToServer(): PDO;

    public function isConnected(): bool
    {
        return !empty($this->connection);
    }

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
        if (!$this->isConnected()) {
            $this->connection = $this->connectToServer();
        }

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

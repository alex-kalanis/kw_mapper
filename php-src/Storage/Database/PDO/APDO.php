<?php

namespace kalanis\kw_mapper\Storage\Database\PDO;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\ASQL;
use PDO;
use PDOStatement;


/**
 * Class APDO
 * @package kalanis\kw_mapper\Storage\Database\PDO
 * PHP data object abstraction
 * Uses placeholders, not question marks
 */
abstract class APDO extends ASQL
{
    /** @var PDO|null */
    protected $connection = null;
    /** @var PDOStatement|null */
    protected $lastStatement;

    /**
     * @param string $query
     * @param array<string, mixed> $params
     * @param int $fetchType
     * @throws MapperException
     * @return array<string|int, array<int, string|int|float>>
     */
    public function query(string $query, array $params, int $fetchType = PDO::FETCH_ASSOC): array
    {
        if (empty($query)) {
            return [];
        }

        $this->connect();

//print_r(['qu', str_split($query, 80), $params]);
        $statement = $this->connection->prepare($query);
        $statement->execute($params);

        $this->lastStatement = $statement;

        $result = $statement->fetchAll($fetchType);
        return (false !== $result) ? $result : [];
    }

    /**
     * @param string $query
     * @param array<string, mixed> $params
     * @throws MapperException
     * @return bool
     */
    public function exec(string $query, array $params): bool
    {
        if (empty($query)) {
            return false;
        }

        $this->connect();

        $statement = $this->connection->prepare($query);
        $result = $statement->execute($params);

        $this->lastStatement = $statement;

        return $result && $statement->closeCursor();
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
        /** @scrutinizer ignore-call */
        $id = $this->connection->lastInsertId();
        return (false === $id) ? null : strval($id);
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

        return (bool) $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return (bool) $this->connection->commit();
    }

    public function rollBack(): bool
    {
        return (bool) $this->connection->rollBack();
    }
}

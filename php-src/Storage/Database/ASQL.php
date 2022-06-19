<?php

namespace kalanis\kw_mapper\Storage\Database;


use kalanis\kw_mapper\Interfaces\IPassConnection;
use kalanis\kw_mapper\MapperException;


/**
 * Class ASQL
 * @package kalanis\kw_mapper\Storage\Database
 * Dummy connector to any SQL database which implements following requirements
 */
abstract class ASQL extends ADatabase implements IPassConnection
{
    use TConnection;

    /**
     * Get content from DB
     * SELECT ...
     * @param string $query
     * @param array<string, int|string|float|null> $params
     * @throws MapperException
     * @return array<string|int, array<int, string|int|float>>
     */
    abstract public function query(string $query, array $params): array;

    /**
     * Execute query over DB
     * INSERT, UPDATE, DELETE, ...
     * @param string $query
     * @param array<string, int|string|float|null> $params
     * @throws MapperException
     * @return bool
     */
    abstract public function exec(string $query, array $params): bool;

    /**
     * Returns ID of last inserted statement
     * @return string|null
     */
    abstract public function lastInsertId(): ?string;

    /**
     * Returns number of affected rows
     * @return int|null
     */
    abstract public function rowCount(): ?int;

    /**
     * Initiates a transaction
     * @return bool
     */
    abstract public function beginTransaction(): bool;

    /**
     * Commits a transaction
     * @return bool
     */
    abstract public function commit(): bool;

    /**
     * When came problem with transaction
     * @return bool
     */
    abstract public function rollBack(): bool;
}

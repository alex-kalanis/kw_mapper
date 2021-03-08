<?php

namespace kalanis\kw_mapper\Storage\Database;


/**
 * Class ADatabase
 * @package kalanis\kw_mapper\Storage\Database
 * Dummy connector to any database which implements following requirements
 */
abstract class ADatabase
{
    /** @var Config */
    protected $config = null;
    /** @var string[]|int[] */
    protected $attributes = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Add another attributes which will be set after db connection
     * @param $attribute
     * @param $value
     */
    public function addAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    /**
     * Returns string representation of language dialect class for query builder
     * @return string
     */
    abstract public function languageDialect(): string;

    /**
     * Get content from DB
     * SELECT ...
     * @param string $query
     * @param string[] $params
     * @return string[]
     */
    abstract public function query(string $query, array $params): array;

    /**
     * Execute query over DB
     * INSERT, UPDATE, DELETE, ...
     * @param string $query
     * @param string[] $params
     * @return bool
     */
    abstract public function exec(string $query, array $params): bool;

    /**
     * Check if system knows detail about connection
     * @return bool
     */
    abstract public function isConnected(): bool;

    /**
     * Reset details about connection
     */
    abstract public function reconnect(): void;

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

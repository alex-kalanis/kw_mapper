<?php

namespace kalanis\kw_mapper\Storage\Database\Raw;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\ASQL;
use kalanis\kw_mapper\Storage\Database\Dialects;
use kalanis\kw_mapper\Storage\Database\TBindNames;


/**
 * Class Oracle
 * @package kalanis\kw_mapper\Storage\Database\Raw
 * Secondary connector to Oracle DB; use when the primary one under PDO has problems
 * @codeCoverageIgnore remote connection
 */
class Oracle extends ASQL
{
    use TBindNames;

    protected string $extension = 'oci8';
    /** @var resource|null */
    protected $connection = null;
    /** @var resource|null */
    protected $lastStatement = null;

    protected bool $autoCommit = true;

    public function disconnect(): void
    {
        if ($this->isConnected()) {
            \oci_close($this->connection);
        }
        $this->connection = null;
    }

    public function languageDialect(): string
    {
        return Dialects\Oracle::class;
    }

    public function query(string $query, array $params): array
    {
        if (empty($query)) {
            return [];
        }

        $this->connect();

        $statement = \oci_parse($this->connection, $query);
        if (false === $statement) {
            $err = \oci_error();
            throw new MapperException('oci8 parse error: ' . (!empty($err['message']) ? strval($err['message']) : 'error also has error'));
        }
        foreach ($params as $paramName => $paramValue) {
            \oci_bind_by_name($statement, $paramName, $params[$paramName], -1, $this->getType($paramValue));
        }
        if (!\oci_execute($statement, $this->getExecMode())) {
            $err = \oci_error($statement);
            throw new MapperException('oci8 connection error: ' . (!empty($err['message']) ? strval($err['message']) : 'error also has error'));
        };
        $results = [];
        \oci_fetch_all($statement, $results, 0, -1, OCI_ASSOC);

        $this->lastStatement = $statement;

        return $results;
    }

    public function exec(string $query, array $params): bool
    {
        if (empty($query)) {
            return false;
        }

        $this->connect();

        $statement = \oci_parse($this->connection, $query);
        if (false === $statement) {
            $err = \oci_error();
            throw new MapperException('oci8 parse error: ' . (!empty($err['message']) ? strval($err['message']) : 'error also has error'));
        }
        foreach ($params as $paramName => $paramValue) {
            \oci_bind_by_name($statement, $paramName, $params[$paramName], -1, $this->getType($paramValue));
        }
        $this->lastStatement = $statement;

        if (!\oci_execute($statement, $this->getExecMode())) {
            $err = \oci_error($statement);
            throw new MapperException('oci8 connection error: ' . (!empty($err['message']) ? strval($err['message']) : 'error also has error'));
        }
        return true;
    }

    /**
     * @param mixed $var
     * @return int
     */
    protected function getType($var): int
    {
        if (is_bool($var)) {
            return OCI_B_BOL;
        } elseif (is_int($var)) {
            return OCI_B_INT;
        } elseif (is_float($var)) {
            return OCI_B_NUM;
        } else {
            return SQLT_CHR;
        }
    }

    protected function getExecMode(): int
    {
        return $this->autoCommit ? OCI_COMMIT_ON_SUCCESS : OCI_NO_AUTO_COMMIT;
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
     * @throws MapperException
     * @return resource
     */
    protected function connectToServer()
    {
        $connection = \oci_connect(
            $this->config->getUser(),
            $this->config->getPassword(),
            $this->config->getLocation(),
        );
        if (false === $connection) {
            $err = \oci_error();
            throw new MapperException('oci8 connection error: ' . (!empty($err['message']) ? strval($err['message']) : 'error also has error'));
        }

        return $connection;
    }

    public function lastInsertId(): ?string
    {
        return null;
    }

    public function rowCount(): ?int
    {
        return $this->lastStatement ? intval(\oci_num_rows($this->lastStatement)) : null ;
    }

    public function beginTransaction(): bool
    {
        if (!$this->isConnected()) {
            $this->connection = $this->connectToServer();
        }

        $this->autoCommit = false;
        return true;
    }

    public function commit(): bool
    {
        $result = \oci_commit($this->connection);
        $this->autoCommit = true;
        return $result;
    }

    public function rollBack(): bool
    {
        $result = \oci_rollback($this->connection);
        $this->autoCommit = true;
        return $result;
    }
}

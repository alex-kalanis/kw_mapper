<?php

namespace kalanis\kw_mapper\Storage\Database\Raw;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\ASQL;


/**
 * Class MySQLi
 * @package kalanis\kw_mapper\Storage\Database
 * Problematic connector to MySQL just for compatibility - USE PDO instead!!!
 */
class MySQLi extends ASQL
{
    /** @var \mysqli|null */
    protected $connection = null;
    /** @var \mysqli_stmt|null */
    protected $lastStatement;

    public function __destruct()
    {
        if ($this->isConnected()) {
            $this->connection->close();
        }
    }

    public function languageDialect(): string
    {
        return '\kalanis\kw_mapper\Storage\Database\Dialects\MySQL';
    }

    public function reconnect(): void
    {
        $this->connection = null;
    }

    public function query(string $query, array $params, int $fetchType = MYSQLI_ASSOC): array
    {
        if (empty($query)) {
            return [];
        }

        if (!$this->isConnected()) {
            $this->connection = $this->connectToServer();
        }

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

        if (!$this->isConnected()) {
            $this->connection = $this->connectToServer();
        }

        $statement = $this->connection->stmt_init();
        list($updQuery, $binds, $types) = $this->bindFromNamedToQuestions($query, $params);
        $statement->prepare($updQuery);
        if (!empty($binds)) {
            $statement->bind_param(implode('', $types), ...$binds);
        }
        $this->lastStatement = $statement;

        return $statement->execute();
    }

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
            throw new \RuntimeException('mysqli connection error: ' . $connection->connect_error);
        }

//        foreach ($this->attributes as $key => $value){
//            $connection->setAttribute($key, $value);
//        }

        $connection->set_charset('utf8');
        if ($connection->errno) {
            throw new \RuntimeException('mysqli error: ' . $connection->error);
        }
        $connection->query('SET NAMES utf8;');

        return $connection;
    }

    /**
     * @param string $query
     * @param array $params
     * @return array
     * @throws MapperException
     */
    protected function bindFromNamedToQuestions(string $query, array $params): array
    {
        $binds = [];
        $types = [];
        if (empty($params)) {
            return [$query, $binds, $types];
        }
        while (false !== ($pos = strpos($query, ':'))) {
            $nextSpace = strpos($query, ' ', $pos);
            $key = ($nextSpace) ? substr($query, $pos, $nextSpace) : substr($query, $pos);
            if (!isset($params[$key])) {
                throw new MapperException(sprintf('Unknown bind for key *%s*', $key));
            }
            $binds[] = $params[$key];
            $types[] = $this->getTypeOf($params[$key]);
            $query = substr($query, 0, $pos) . '?' . ( $nextSpace ? substr($query, $nextSpace) : '' );
        }
        return [$query, $binds, $types];
    }

    protected function getTypeOf($var): string
    {
        if (is_bool($var)) {
            return 'i';
        } elseif (is_int($var)) {
            return 'i';
        } elseif (is_float($var)) {
            return 'd';
        } else {
            return 's';
        }
    }

    public function isConnected(): bool
    {
        return !empty($this->connection);
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

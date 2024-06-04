<?php

namespace kalanis\kw_mapper\Storage\Shared\QueryBuilder;


class Condition
{
    /** @var string|string[]|callable|null */
    protected $raw = null; // can be either column name or full query
    protected string $tableName = '';
    /** @var string|int */
    protected $columnName = '';
    protected string $operation = '';
    /** @var string|string[] */
    protected $columnKey = '';

    /**
     * @param string $tableName
     * @param string|int $columnName
     * @param string $operation
     * @param string|string[] $columnKey
     * @return $this
     */
    public function setData(string $tableName, $columnName, string $operation, $columnKey): self
    {
        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->operation = $operation;
        $this->columnKey = $columnKey;
        $this->raw = null;
        return $this;
    }

    /**
     * @param string|string[]|callable $operation
     * @param string|string[] $columnKey
     * @return $this
     */
    public function setRaw($operation, $columnKey = ''): self
    {
        $this->tableName = '';
        $this->columnName = '';
        $this->operation = '';
        $this->columnKey = $columnKey;
        $this->raw = $operation;
        return $this;
    }

    public function isRaw(): bool
    {
        return !is_null($this->raw);
    }

    /**
     * @return callable|string|string[]|null
     */
    public function getRaw()
    {
        return $this->raw;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return string|int
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @return string|string[]
     */
    public function getColumnKey()
    {
        return $this->columnKey;
    }
}

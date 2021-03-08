<?php

namespace kalanis\kw_mapper\Storage\Shared\QueryBuilder;


class Condition
{
    /** @var string */
    protected $tableName = '';
    /** @var string|int */
    protected $columnName = '';
    /** @var string */
    protected $operation = '';
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
        return $this;
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

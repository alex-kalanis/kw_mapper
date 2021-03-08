<?php

namespace kalanis\kw_mapper\Storage\Shared\QueryBuilder;


class Property
{
    /** @var string */
    protected $tableName = '';
    /** @var string|int */
    protected $columnName = '';
    /** @var string */
    protected $columnKey = '';

    /**
     * @param string $tableName
     * @param string|int $columnName
     * @param string $columnKey
     * @return $this
     */
    public function setData(string $tableName, $columnName, string $columnKey): self
    {
        $this->tableName = $tableName;
        $this->columnName = $columnName;
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

    public function getColumnKey(): string
    {
        return $this->columnKey;
    }
}

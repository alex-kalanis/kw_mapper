<?php

namespace kalanis\kw_mapper\Storage\Shared\QueryBuilder;


class Group
{
    protected string $tableName = '';
    /** @var string|int */
    protected $columnName = '';

    /**
     * @param string $tableName
     * @param string|int $columnName
     * @return $this
     */
    public function setData(string $tableName, $columnName): self
    {
        $this->tableName = $tableName;
        $this->columnName = $columnName;
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
}

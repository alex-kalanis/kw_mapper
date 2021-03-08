<?php

namespace kalanis\kw_mapper\Storage\Shared\QueryBuilder;


class Column
{
    /** @var string */
    protected $tableName = '';
    /** @var string|int */
    protected $columnName = '';
    /** @var string */
    protected $columnAlias = '';
    /** @var string */
    protected $aggregate = '';

    /**
     * @param string $tableName
     * @param string|int $columnName
     * @param string $columnAlias
     * @param string $aggregate
     * @return $this
     */
    public function setData(string $tableName, $columnName, string $columnAlias, string $aggregate = ''): self
    {
        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->columnAlias = $columnAlias;
        $this->aggregate = $aggregate;
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

    public function getColumnAlias(): string
    {
        return $this->columnAlias;
    }

    public function getAggregate(): string
    {
        return $this->aggregate;
    }
}

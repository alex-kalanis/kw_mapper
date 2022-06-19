<?php

namespace kalanis\kw_mapper\Storage\Shared\QueryBuilder;


class Order
{
    /** @var string */
    protected $tableName = '';
    /** @var string|int */
    protected $columnName = '';
    /** @var string */
    protected $direction = '';

    /**
     * @param string $tableName
     * @param string|int $columnName
     * @param string $direction
     * @return $this
     */
    public function setData(string $tableName, $columnName, string $direction = ''): self
    {
        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->direction = $direction;
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

    public function getDirection(): string
    {
        return $this->direction;
    }
}

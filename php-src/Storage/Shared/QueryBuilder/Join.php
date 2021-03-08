<?php

namespace kalanis\kw_mapper\Storage\Shared\QueryBuilder;


class Join
{
    /** @var string */
    protected $newTableName = '';
    /** @var string */
    protected $knownTableName = '';
    /** @var string|int */
    protected $newColumnName = '';
    /** @var string|int */
    protected $knownColumnName = '';
    /** @var string */
    protected $joinUnderAlias = '';
    /** @var string */
    protected $side = '';
    /** @var string */
    protected $tableAlias = '';

    /**
     * @param string $joinUnderAlias
     * @param string $addTableName
     * @param string|int $addColumnName
     * @param string $knownTableName
     * @param string|int $knownColumnName
     * @param string $side
     * @param string $tableAlias
     * @return $this
     */
    public function setData(string $joinUnderAlias, string $addTableName, $addColumnName, string $knownTableName, $knownColumnName, string $side = '', string $tableAlias = ''): self
    {
        $this->joinUnderAlias = $joinUnderAlias;
        $this->newTableName = $addTableName;
        $this->newColumnName = $addColumnName;
        $this->knownTableName = $knownTableName;
        $this->knownColumnName = $knownColumnName;
        $this->side = $side;
        $this->tableAlias = $tableAlias;
        return $this;
    }

    public function getJoinUnderAlias(): string
    {
        return $this->joinUnderAlias;
    }

    public function getNewTableName(): string
    {
        return $this->newTableName;
    }

    public function getKnownTableName(): string
    {
        return $this->knownTableName;
    }

    /**
     * @return string|int
     */
    public function getNewColumnName()
    {
        return $this->newColumnName;
    }

    /**
     * @return string|int
     */
    public function getKnownColumnName()
    {
        return $this->knownColumnName;
    }

    public function getSide(): string
    {
        return $this->side;
    }

    public function getTableAlias(): string
    {
        return $this->tableAlias;
    }
}

<?php

namespace kalanis\kw_mapper\Storage\Shared;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;


/**
 * Class QueryBuilder
 * @package kalanis\kw_mapper\Storage\Shared
 * Prepare conditions, properties, joins and params as extra parts to pass to the storage
 */
class QueryBuilder
{
    protected static $uniqId = 0;
    /** @var QueryBuilder\Column */
    protected $column = null;
    /** @var QueryBuilder\Condition */
    protected $condition = null;
    /** @var QueryBuilder\Property */
    protected $property = null;
    /** @var QueryBuilder\Join */
    protected $join = null;
    /** @var QueryBuilder\Order */
    protected $order = null;
    /** @var QueryBuilder\Group */
    protected $group = null;

    protected $relation = IQueryBuilder::RELATION_AND;
    protected $baseTable = '';
    protected $params = [];
    /** @var QueryBuilder\Column[] */
    protected $columns = [];
    /** @var QueryBuilder\Join[] */
    protected $joins = [];
    /** @var QueryBuilder\Condition[] */
    protected $conditions = [];
    /** @var QueryBuilder\Property[] */
    protected $properties = [];
    /** @var QueryBuilder\Order[] */
    protected $ordering = [];
    /** @var QueryBuilder\Group[] */
    protected $grouping = [];
    /** @var int|null */
    protected $offset = null;
    /** @var int|null */
    protected $limit = null;

    public function __construct()
    {
        $this->column = new QueryBuilder\Column();
        $this->condition = new QueryBuilder\Condition();
        $this->property = new QueryBuilder\Property();
        $this->join = new QueryBuilder\Join();
        $this->order = new QueryBuilder\Order();
        $this->group = new QueryBuilder\Group();
    }

    /**
     * @param string $tableName
     * @param string|int $columnName
     * @param string $alias
     * @param string $aggregate
     * @throws MapperException
     */
    public function addColumn(string $tableName, $columnName, string $alias = '', string $aggregate = ''): void
    {
        if (!empty($aggregate) && !in_array($aggregate, [
                IQueryBuilder::AGGREGATE_AVG, IQueryBuilder::AGGREGATE_COUNT,
                IQueryBuilder::AGGREGATE_MIN, IQueryBuilder::AGGREGATE_MAX,
                IQueryBuilder::AGGREGATE_SUM,
            ])) {
            throw new MapperException(sprintf('Unknown aggregation by method *%s* !', $aggregate));
        }
        $column = clone $this->column;
        $this->columns[] = $column->setData($tableName, $columnName, $alias, $aggregate);
    }

    /**
     * @param string $tableName
     * @param string|int $columnName
     * @param string $operation
     * @param mixed $value
     * @throws MapperException
     */
    public function addCondition(string $tableName, $columnName, string $operation, $value = null): void
    {
        if (!empty($operation) && !in_array($operation, [
                IQueryBuilder::OPERATION_NULL, IQueryBuilder::OPERATION_NNULL,
                IQueryBuilder::OPERATION_EQ, IQueryBuilder::OPERATION_NEQ,
                IQueryBuilder::OPERATION_GT, IQueryBuilder::OPERATION_GTE,
                IQueryBuilder::OPERATION_LT, IQueryBuilder::OPERATION_LTE,
                IQueryBuilder::OPERATION_LIKE, IQueryBuilder::OPERATION_NLIKE,
                IQueryBuilder::OPERATION_REXP,
                IQueryBuilder::OPERATION_IN, IQueryBuilder::OPERATION_NIN,
            ])) {
            throw new MapperException(sprintf('Unknown operation *%s* !', $operation));
        }
        $condition = clone $this->condition;
        $columnKey = sprintf(':%s_%s', $columnName, static::$uniqId);
        $this->conditions[] = $condition->setData($tableName, $columnName, $operation, $columnKey);
        $this->params[$columnKey] = $value;
        static::$uniqId++;
    }

    public function addProperty(string $tableName, $columnName, $value = null): void
    {
        $property = clone $this->property;
        $columnKey = sprintf(':%s_%s', $columnName, static::$uniqId);
        $this->properties[] = $property->setData($tableName, $columnName, $columnKey);
        $this->params[$columnKey] = $value;
        static::$uniqId++;
    }

    /**
     * @param string $joinUnderAlias
     * @param string $addTableName
     * @param string|int $addColumnName
     * @param string $knownTableName
     * @param string|int $knownColumnName
     * @param string $side
     * @param string $tableAlias
     */
    public function addJoin(string $joinUnderAlias, string $addTableName, $addColumnName, string $knownTableName, $knownColumnName, string $side = '', string $tableAlias = ''): void
    {
        $join = clone $this->join;
        $this->joins[] = $join->setData($joinUnderAlias, $addTableName, $addColumnName, $knownTableName, $knownColumnName, $side, $tableAlias);
    }

    public function addOrderBy(string $tableName, $columnName, string $direction): void
    {
        $order = clone $this->order;
        $this->ordering[] = $order->setData($tableName, $columnName, $direction);
    }

    public function addGroupBy(string $tableName, $columnName): void
    {
        $group = clone $this->group;
        $this->grouping[] = $group->setData($tableName, $columnName);
    }

    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
    }

    public function setOffset(?int $offset): void
    {
        $this->offset = $offset;
    }

    public function setLimits(?int $offset, ?int $limit): void
    {
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function setRelations(string $relation): void
    {
        if (IQueryBuilder::RELATION_AND == $relation || IQueryBuilder::RELATION_OR == $relation) {
            $this->relation = $relation;
        }
    }

    public function clear(): void
    {
        $this->params = [];
        $this->columns = [];
        $this->conditions = [];
        $this->properties = [];
        $this->ordering = [];
        $this->grouping = [];
        $this->joins = [];
        $this->offset = null;
        $this->limit = null;
    }

    public function clearColumns(): void
    {
        $this->columns = [];
    }

    public function setBaseTable(string $tableName): void
    {
        $this->baseTable = $tableName;
    }

    public function getRelation(): string
    {
        return $this->relation;
    }

    public function getBaseTable(): string
    {
        return $this->baseTable;
    }

    /**
     * @return string[]|int[]
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return QueryBuilder\Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return QueryBuilder\Join[]
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @return QueryBuilder\Condition[]
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @return QueryBuilder\Property[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return QueryBuilder\Order[]
     */
    public function getOrdering(): array
    {
        return $this->ordering;
    }

    /**
     * @return QueryBuilder\Group[]
     */
    public function getGrouping(): array
    {
        return $this->grouping;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

}

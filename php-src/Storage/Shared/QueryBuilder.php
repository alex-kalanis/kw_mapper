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
    protected static int $uniqId = 0;
    protected QueryBuilder\Column $column;
    protected QueryBuilder\Condition $condition;
    protected QueryBuilder\Property $property;
    protected QueryBuilder\Join $join;
    protected QueryBuilder\Order $order;
    protected QueryBuilder\Group $group;

    protected string $relation = IQueryBuilder::RELATION_AND;
    protected string $baseTable = '';
    /** @var array<string, int|string|float|null> */
    protected array $params = [];
    /** @var QueryBuilder\Column[] */
    protected array $columns = [];
    /** @var QueryBuilder\Join[] */
    protected array $joins = [];
    /** @var QueryBuilder\Condition[] */
    protected array $conditions = [];
    /** @var QueryBuilder\Property[] */
    protected array $properties = [];
    /** @var QueryBuilder\Order[] */
    protected array $ordering = [];
    /** @var QueryBuilder\Group[] */
    protected array $grouping = [];
    /** @var QueryBuilder\Condition[] */
    protected array $having = [];
    protected ?int $offset = null;
    protected ?int $limit = null;

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
     * @param string|int $alias
     * @param string $aggregate
     * @throws MapperException
     */
    public function addColumn(string $tableName, $columnName, $alias = '', string $aggregate = ''): void
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

        if (in_array($operation, [IQueryBuilder::OPERATION_EQ, IQueryBuilder::OPERATION_LIKE, IQueryBuilder::OPERATION_IN]) && is_null($value)) {
            $operation = IQueryBuilder::OPERATION_NULL;
        } elseif (in_array($operation, [IQueryBuilder::OPERATION_NEQ, IQueryBuilder::OPERATION_NLIKE, IQueryBuilder::OPERATION_NIN]) && is_null($value)) {
            $operation = IQueryBuilder::OPERATION_NNULL;
        }

        $condition = clone $this->condition;
        if (is_null($value)) {
            $this->conditions[] = $condition->setData($tableName, $columnName, $operation, $this->simpleNoValue($columnName));
        } else {
            $this->conditions[] = $condition->setData($tableName, $columnName, $operation, $this->multipleByValue($columnName, $value));
        }
    }

    /**
     * @param string|string[]|callable $operation
     * @param string $prefix
     * @param mixed $value
     */
    public function addRawCondition($operation, string $prefix = '', $value = null): void
    {
        $condition = clone $this->condition;
        if (is_null($value)) {
            $this->conditions[] = $condition->setRaw($operation);
        } else {
            $this->conditions[] = $condition->setRaw($operation, $this->multipleByValue($prefix, $value));
        }
    }

    /**
     * @param string $tableName
     * @param string|int $columnName
     * @param mixed $value
     */
    public function addProperty(string $tableName, $columnName, $value = null): void
    {
        $property = clone $this->property;
        $this->properties[] = $property->setData($tableName, $columnName, $this->simpleByValue($columnName, $value));
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

    /**
     * @param string $tableName
     * @param string|int $columnName
     * @param string $operation
     * @param string|int|float|null $value
     * @throws MapperException
     */
    public function addHavingCondition(string $tableName, $columnName, string $operation, $value = null): void
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

        if (in_array($operation, [IQueryBuilder::OPERATION_EQ, IQueryBuilder::OPERATION_LIKE, IQueryBuilder::OPERATION_IN]) && is_null($value)) {
            $operation = IQueryBuilder::OPERATION_NULL;
        } elseif (in_array($operation, [IQueryBuilder::OPERATION_NEQ, IQueryBuilder::OPERATION_NLIKE, IQueryBuilder::OPERATION_NIN]) && is_null($value)) {
            $operation = IQueryBuilder::OPERATION_NNULL;
        }

        $condition = clone $this->condition;
        if (is_null($value)) {
            $this->having[] = $condition->setData($tableName, $columnName, $operation, $this->simpleNoValue($columnName));
        } else {
            $this->having[] = $condition->setData($tableName, $columnName, $operation, $this->multipleByValue($columnName, $value));
        }
    }

    /**
     * @param string|int $columnName
     * @param string|int|float|array<string|int|float|null>|null $value
     * @return string|string[]
     */
    protected function multipleByValue($columnName, $value)
    {
        if (is_array($value)) {
            $keys = [];
            foreach ($value as $item) {
                $keys[] = $this->simpleByValue($columnName, $item);
            }
            return $keys;
        } else {
            return $this->simpleByValue($columnName, $value);
        }
    }

    /**
     * @param string|int $columnName
     * @param string|int|float|null $value
     * @return string
     */
    protected function simpleByValue($columnName, $value): string
    {
        $columnKey = $this->simpleNoValue($columnName);
        $this->params[$columnKey] = is_null($value) ? null : strval($value);
        return $columnKey;
    }

    /**
     * @param string|int $columnName
     * @return string
     */
    protected function simpleNoValue($columnName): string
    {
        $value = sprintf(':%s_%s', $columnName, static::$uniqId);
        static::$uniqId++;
        return $value;
    }

    /**
     * @param string $tableName
     * @param string|int $columnName
     * @param string $direction
     * @throws MapperException
     */
    public function addOrderBy(string $tableName, $columnName, string $direction): void
    {
        if (!empty($direction) && !in_array($direction, [
                IQueryBuilder::ORDER_ASC, IQueryBuilder::ORDER_DESC,
            ])) {
            throw new MapperException(sprintf('Unknown direction *%s* !', $direction));
        }
        $order = clone $this->order;
        $this->ordering[] = $order->setData($tableName, $columnName, $direction);
    }

    /**
     * @param string $tableName
     * @param string|int $columnName
     */
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
        $this->having = [];
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
     * @return array<string, int|string|float|null>
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

    /**
     * @return QueryBuilder\Condition[]
     */
    public function getHavingCondition(): array
    {
        return $this->having;
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

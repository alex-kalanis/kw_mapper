<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Class ADialect
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * All actions as defined by CRUD - Create, Read, Update, Delete
 *
 * Hints:
 * For testing purposes we just fill prepared data and by that we got query. Implemention details are the problem
 * of dialect of each language. And it's simple to test that. Then result go to the real connection.
 *
 * @todo:
 * -> database operations - table create, table drop, table alter, ...
 *
 * Escaping of params has been determined on following links:
 * @link http://sqlfiddle.com/
 * @link https://sqliteonline.com
 */
abstract class ADialect
{
    /**
     * Create data by properties
     * @param QueryBuilder $builder
     * @return string|object
     * @throws MapperException
     */
    abstract public function insert(QueryBuilder $builder);

    /**
     * Read data described by conditions
     * @param QueryBuilder $builder
     * @return string|object
     * @throws MapperException
     */
    abstract public function select(QueryBuilder $builder);

    /**
     * Update data properties described by conditions
     * @param QueryBuilder $builder
     * @return string|object
     * @throws MapperException
     */
    abstract public function update(QueryBuilder $builder);

    /**
     * Delete data by conditions
     * @param QueryBuilder $builder
     * @return string|object
     * @throws MapperException
     */
    abstract public function delete(QueryBuilder $builder);

    /**
     * Get table structure
     * @param QueryBuilder $builder
     * @return string|object
     * @throws MapperException
     */
    abstract public function describe(QueryBuilder $builder);

    /**
     * Get array of available join operations
     * @return array
     */
    abstract public function availableJoins(): array;

    /**
     * @param string $operation
     * @return string
     * @throws MapperException
     */
    public function translateOperation(string $operation): string
    {
        switch ($operation) {
            case IQueryBuilder::OPERATION_NULL:
                return 'IS NULL';
            case IQueryBuilder::OPERATION_NNULL:
                return 'IS NOT NULL';
            case IQueryBuilder::OPERATION_EQ:
                return '=';
            case IQueryBuilder::OPERATION_NEQ:
                return '!=';
            case IQueryBuilder::OPERATION_GT:
                return '>';
            case IQueryBuilder::OPERATION_GTE:
                return '>=';
            case IQueryBuilder::OPERATION_LT:
                return '<';
            case IQueryBuilder::OPERATION_LTE:
                return '<=';
            case IQueryBuilder::OPERATION_LIKE:
                return 'LIKE';
            case IQueryBuilder::OPERATION_NLIKE:
                return 'NOT LIKE';
            case IQueryBuilder::OPERATION_REXP:
                return 'REGEX';
            case IQueryBuilder::OPERATION_IN:
                return 'IN';
            case IQueryBuilder::OPERATION_NIN:
                return 'NOT IN';
            default:
                throw new MapperException(sprintf('Unknown operation %s', $operation));
        }
    }

    /**
     * @param string $operation
     * @param string|string[] $columnKey
     * @return string
     * @throws MapperException
     */
    public function translateKey(string $operation, $columnKey): string
    {
        switch ($operation) {
            case IQueryBuilder::OPERATION_NULL:
            case IQueryBuilder::OPERATION_NNULL:
                return '';
            case IQueryBuilder::OPERATION_EQ:
            case IQueryBuilder::OPERATION_NEQ:
            case IQueryBuilder::OPERATION_GT:
            case IQueryBuilder::OPERATION_GTE:
            case IQueryBuilder::OPERATION_LT:
            case IQueryBuilder::OPERATION_LTE:
            case IQueryBuilder::OPERATION_LIKE:
            case IQueryBuilder::OPERATION_NLIKE:
            case IQueryBuilder::OPERATION_REXP:
                return strval($columnKey);
            case IQueryBuilder::OPERATION_IN:
            case IQueryBuilder::OPERATION_NIN:
                return sprintf('(%s)', implode(',', $this->notEmptyArray($columnKey)));
            default:
                throw new MapperException(sprintf('Unknown operation *%s*', $operation));
        }
    }

    protected function notEmptyArray($array): array
    {
        if (empty($array)) {
            return [0];
        }
        return (array)$array;
    }

    /**
     * @param QueryBuilder\Column[] $columns
     * @return string
     */
    public function makeColumns(array $columns): string
    {
        if (empty($columns)) {
            return $this->selectAllColumns();
        }
        return implode(', ', array_map([$this, 'singleColumn'], $columns));
    }

    protected function selectAllColumns(): string
    {
        return '*';
    }

    public function singleColumn(QueryBuilder\Column $column): string
    {
        $alias = empty($column->getColumnAlias()) ? '' : sprintf(' AS %s', $column->getColumnAlias());
        $where = empty($column->getTableName())
            ? sprintf('%s', $column->getColumnName() )
            : sprintf('%s.%s', $column->getTableName(), $column->getColumnName())
        ;
        return empty($column->getAggregate())
            ? sprintf('%s%s', $where, $alias )
            : sprintf('%s(%s)%s', $column->getAggregate(), $where, $alias )
        ;
    }

    /**
     * @param QueryBuilder\Property[] $properties
     * @return string
     */
    public function makeProperty(array $properties): string
    {
        if (empty($properties)) {
            return $this->selectAllProperties();
        }
        return implode(', ', array_map([$this, 'singleProperty'], $properties));
    }

    protected function selectAllProperties(): string
    {
        return '1=1';
    }

    public function singleProperty(QueryBuilder\Property $column): string
    {
        return sprintf('%s = %s',
            $column->getColumnName(),
            $column->getColumnKey() // PDO key in behalf of value
        );
    }

    /**
     * @param QueryBuilder\Property[] $properties
     * @return string
     * @throws MapperException
     */
    public function makePropertyList(array $properties): string
    {
        if (empty($properties)) {
            throw new MapperException('Empty property list!');
        }
        return implode(', ', array_map([$this, 'singlePropertyListed'], $properties));
    }

    public function singlePropertyListed(QueryBuilder\Property $column): string
    {
        return empty($column->getTableName())
            ? sprintf('%s', $column->getColumnName() )
            : sprintf('%s.%s', $column->getTableName(), $column->getColumnName() )
        ;
    }

    /**
     * @param QueryBuilder\Property[] $properties
     * @return string
     * @throws MapperException
     */
    public function makePropertyEntries(array $properties): string
    {
        if (empty($properties)) {
            throw new MapperException('Empty property list!');
        }
        return implode(', ', array_map([$this, 'singlePropertyEntry'], $properties));
    }

    public function singlePropertyEntry(QueryBuilder\Property $column): string
    {
        return $column->getColumnKey();
    }

    /**
     * @param QueryBuilder\Condition[] $conditions
     * @param string $relation
     * @return string
     */
    public function makeConditions(array $conditions, string $relation): string
    {
        if (empty($conditions)) {
            return '';
        }
        return ' WHERE ' . implode(' ' . $relation . ' ', array_map([$this, 'singleCondition'], $conditions));
    }

    /**
     * @param QueryBuilder\Condition $condition
     * @return string
     * @throws MapperException
     */
    public function singleCondition(QueryBuilder\Condition $condition): string
    {
        return empty($condition->getTableName())
            ? sprintf('%s %s %s',
                $condition->getColumnName(),
                $this->translateOperation($condition->getOperation()),
                $this->translateKey($condition->getOperation(), $condition->getColumnKey())
            )
            : sprintf('%s.%s %s %s',
                $condition->getTableName(),
                $condition->getColumnName(),
                $this->translateOperation($condition->getOperation()),
                $this->translateKey($condition->getOperation(), $condition->getColumnKey())
            )
        ;
    }

    /**
     * @param QueryBuilder\Order[] $ordering
     * @return string
     */
    public function makeOrdering(array $ordering): string
    {
        if (empty($ordering)) {
            return '';
        }
        return ' ORDER BY ' . implode(', ', array_map([$this, 'singleOrder'], $ordering));
    }

    public function singleOrder(QueryBuilder\Order $order): string
    {
        return empty($order->getTableName())
            ? sprintf('%s %s', $order->getColumnName(), $order->getDirection() )
            : sprintf('%s.%s %s', $order->getTableName(), $order->getColumnName(), $order->getDirection() )
        ;
    }

    /**
     * @param QueryBuilder\Group[] $groups
     * @return string
     */
    public function makeGrouping(array $groups): string
    {
        if (empty($groups)) {
            return '';
        }
        return ' GROUP BY ' . implode(', ', array_map([$this, 'singleGroup'], $groups));
    }

    public function singleGroup(QueryBuilder\Group $group): string
    {
        return empty($group->getTableName())
            ? sprintf('%s', $group->getColumnName())
            : sprintf('%s.%s',
                $group->getTableName(),
                $group->getColumnName()
            )
        ;
    }

    /**
     * @param QueryBuilder\Join[] $join
     * @return string
     */
    public function makeJoin(array $join): string
    {
        if (empty($join)) {
            return '';
        }
        return implode(' ', array_map([$this, 'singleJoin'], $join));
    }

    public function singleJoin(QueryBuilder\Join $join): string
    {
        return sprintf(' %s JOIN %s%s ON (%s.%s = %s.%s)',
            $join->getSide(),
            $join->getNewTableName(),
            empty($join->getTableAlias()) ? '' : sprintf(' AS %s', $join->getTableAlias()),
            $join->getKnownTableName(),
            $join->getKnownColumnName(),
            empty($join->getTableAlias()) ? $join->getNewTableName() : $join->getTableAlias(),
            $join->getNewColumnName()
        );
    }
}

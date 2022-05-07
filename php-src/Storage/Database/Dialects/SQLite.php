<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Class SQLite
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * Create queries for SQLite - when you save it to the file and yet have file which has it all
 * There is a lot of ignored things - mainly statements without table name for queries with joins
 */
class SQLite extends AEscapedDialect
{
    /**
     * @param QueryBuilder $builder
     * @return string
     * @throws MapperException
     * @link https://www.tutorialspoint.com/sqlite/sqlite_insert_query.htm
     */
    public function insert(QueryBuilder $builder)
    {
        return sprintf('INSERT INTO "%s" (%s) VALUES (%s);',
            $builder->getBaseTable(),
            $this->makePropertyList($builder->getProperties()),
            $this->makePropertyEntries($builder->getProperties())
        );
    }

    public function select(QueryBuilder $builder)
    {
        $joins = $builder->getJoins();
        return sprintf('SELECT %s FROM "%s" %s %s%s%s%s%s;',
            empty($joins) ? $this->makeSimpleColumns($builder->getColumns()) : $this->makeColumns($builder->getColumns()),
            $builder->getBaseTable(),
            empty($joins) ? '' : $this->makeJoin($builder->getJoins()),
            empty($joins) ? $this->makeSimpleConditions($builder->getConditions(), $builder->getRelation()) : $this->makeConditions($builder->getConditions(), $builder->getRelation()),
            empty($joins) ? $this->makeSimpleGrouping($builder->getGrouping()) : $this->makeGrouping($builder->getGrouping()),
            empty($joins) ? $this->makeSimpleHaving($builder->getHavingCondition(), $builder->getRelation()) : $this->makeHaving($builder->getHavingCondition(), $builder->getRelation()),
            empty($joins) ? $this->makeSimpleOrdering($builder->getOrdering()) : $this->makeOrdering($builder->getOrdering()),
            $this->makeLimits($builder->getLimit(), $builder->getOffset())
        );
    }

    /**
     * @param QueryBuilder $builder
     * @return string
     *
     * Beware!!! Cannot use limit - because it's unknown what are the names of primary columns
     * @link https://www.tutorialspoint.com/sqlite/sqlite_update_query.htm
     * @link https://stackoverflow.com/questions/17823018/sqlite-update-limit-case
     */
    public function update(QueryBuilder $builder)
    {
        return sprintf('UPDATE "%s" SET %s%s;',
            $builder->getBaseTable(),
            $this->makeProperty($builder->getProperties()),
            $this->makeSimpleConditions($builder->getConditions(), $builder->getRelation())
        );
    }

    /**
     * @param QueryBuilder $builder
     * @return string
     *
     * Beware!!! Cannot use limit - because it's unknown what are the names of primary columns
     * @link https://www.tutorialspoint.com/sqlite/sqlite_delete_query.htm
     * @link https://stackoverflow.com/questions/1824490/how-do-you-enable-limit-for-delete-in-sqlite
     */
    public function delete(QueryBuilder $builder)
    {
        return sprintf('DELETE FROM "%s"%s;',
            $builder->getBaseTable(),
            $this->makeSimpleConditions($builder->getConditions(), $builder->getRelation())
        );
    }

    public function describe(QueryBuilder $builder)
    {
        return sprintf('SELECT "sql" FROM "sqlite_master" WHERE "name" = \'%s\';', $builder->getBaseTable() );
    }

    /**
     * @param QueryBuilder\Column[] $columns
     * @return string
     */
    public function makeSimpleColumns(array $columns): string
    {
        if (empty($columns)) {
            return $this->selectAllColumns();
        }
        return implode(', ', array_map([$this, 'singleSimpleColumn'], $columns));
    }

    public function singleColumn(QueryBuilder\Column $column): string
    {
        $alias = empty($column->getColumnAlias()) ? '' : sprintf(' AS "%s"', $column->getColumnAlias());
        $where = empty($column->getTableName())
            // @codeCoverageIgnoreStart
            ? sprintf('"%s"', $column->getColumnName() )
            // @codeCoverageIgnoreEnd
            : sprintf('"%s"."%s"', $column->getTableName(), $column->getColumnName())
        ;
        return empty($column->getAggregate())
            ? sprintf('%s%s', $where, $alias )
            : sprintf('%s(%s)%s', $column->getAggregate(), $where, $alias )
            ;
    }

    public function singleSimpleColumn(QueryBuilder\Column $column): string
    {
        $alias = empty($column->getColumnAlias()) ? '' : sprintf(' AS "%s"', $column->getColumnAlias());
        $where = sprintf('"%s"', $column->getColumnName() );
        return empty($column->getAggregate())
            ? sprintf('%s%s', $where, $alias )
            : sprintf('%s(%s)%s', $column->getAggregate(), $where, $alias )
            ;
    }

    public function singleProperty(QueryBuilder\Property $column): string
    {
        return sprintf('"%s" = %s',
            $column->getColumnName(),
            $column->getColumnKey() // PDO key in behalf of value
        );
    }

    public function singlePropertyListed(QueryBuilder\Property $column): string
    {
        return sprintf('"%s"', $column->getColumnName() );
    }

    /**
     * @param QueryBuilder\Condition[] $conditions
     * @param string $relation
     * @return string
     */
    public function makeSimpleConditions(array $conditions, string $relation): string
    {
        if (empty($conditions)) {
            return '';
        }
        return ' WHERE ' . implode(' ' . $relation . ' ', array_map([$this, 'singleSimpleCondition'], $conditions));
    }

    /**
     * @param QueryBuilder\Condition[] $conditions
     * @param string $relation
     * @return string
     */
    public function makeSimpleHaving(array $conditions, string $relation): string
    {
        if (empty($conditions)) {
            return '';
        }
        return ' HAVING ' . implode(' ' . $relation . ' ', array_map([$this, 'singleSimpleCondition'], $conditions));
    }

    /**
     * @param QueryBuilder\Condition $condition
     * @return string
     * @throws MapperException
     */
    public function singleCondition(QueryBuilder\Condition $condition): string
    {
        return empty($condition->getTableName())
            // @codeCoverageIgnoreStart
            ? sprintf('"%s" %s %s',
                $condition->getColumnName(),
                $this->translateOperation($condition->getOperation()),
                $this->translateKey($condition->getOperation(), $condition->getColumnKey())
            )
            // @codeCoverageIgnoreEnd
            : sprintf('"%s"."%s" %s %s',
                $condition->getTableName(),
                $condition->getColumnName(),
                $this->translateOperation($condition->getOperation()),
                $this->translateKey($condition->getOperation(), $condition->getColumnKey())
            )
        ;
    }

    /**
     * @param QueryBuilder\Condition $condition
     * @return string
     * @throws MapperException
     */
    public function singleSimpleCondition(QueryBuilder\Condition $condition): string
    {
        return sprintf('"%s" %s %s',
                $condition->getColumnName(),
                $this->translateOperation($condition->getOperation()),
                $this->translateKey($condition->getOperation(), $condition->getColumnKey())
            );
    }

    /**
     * @param QueryBuilder\Order[] $ordering
     * @return string
     */
    public function makeSimpleOrdering(array $ordering): string
    {
        if (empty($ordering)) {
            return '';
        }
        return ' ORDER BY ' . implode(', ', array_map([$this, 'singleSimpleOrder'], $ordering));
    }

    public function singleOrder(QueryBuilder\Order $order): string
    {
        return empty($order->getTableName())
            // @codeCoverageIgnoreStart
            ? sprintf('"%s" %s', $order->getColumnName(), $order->getDirection() )
            // @codeCoverageIgnoreEnd
            : sprintf('"%s"."%s" %s', $order->getTableName(), $order->getColumnName(), $order->getDirection() )
            ;
    }

    public function singleSimpleOrder(QueryBuilder\Order $order): string
    {
        return sprintf('"%s" %s', $order->getColumnName(), $order->getDirection() );
    }

    public function singleGroup(QueryBuilder\Group $group): string
    {
        return empty($group->getTableName())
            // @codeCoverageIgnoreStart
            ? sprintf('"%s"', $group->getColumnName())
            // @codeCoverageIgnoreEnd
            : sprintf('"%s"."%s"',
                $group->getTableName(),
                $group->getColumnName()
            )
        ;
    }

    /**
     * @param QueryBuilder\Group[] $groups
     * @return string
     */
    public function makeSimpleGrouping(array $groups): string
    {
        if (empty($groups)) {
            return '';
        }
        return ' GROUP BY ' . implode(', ', array_map([$this, 'singleSimpleGroup'], $groups));
    }

    public function singleSimpleGroup(QueryBuilder\Group $group): string
    {
        return sprintf('"%s"', $group->getColumnName());
    }

    public function singleJoin(QueryBuilder\Join $join): string
    {
        return sprintf(' %s JOIN "%s"%s ON ("%s"."%s" = "%s"."%s")',
            $join->getSide(),
            $join->getNewTableName(),
            empty($join->getTableAlias()) ? '' : sprintf(' AS "%s"', $join->getTableAlias()),
            $join->getKnownTableName(),
            $join->getKnownColumnName(),
            empty($join->getTableAlias()) ? $join->getNewTableName() : $join->getTableAlias(),
            $join->getNewColumnName()
        );
    }

    protected function makeLimits(?int $limit, ?int $offset): string
    {
        return is_null($limit)
            ? ''
            : (is_null($offset)
                ? sprintf(' LIMIT %d', $limit)
                : sprintf(' LIMIT %d OFFSET %d', $limit, $offset)
            )
            ;
    }

    public function availableJoins(): array
    {
        return [
            IQueryBuilder::JOIN_BASIC,
            IQueryBuilder::JOIN_INNER,
            IQueryBuilder::JOIN_OUTER,
            IQueryBuilder::JOIN_CROSS,
        ];
    }
}

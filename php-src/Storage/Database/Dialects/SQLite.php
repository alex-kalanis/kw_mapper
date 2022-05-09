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
class SQLite extends ADialect
{
    use TQuotationDialect;

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
            $this->makeSimplePropertyList($builder->getProperties()),
            $this->makePropertyEntries($builder->getProperties())
        );
    }

    public function select(QueryBuilder $builder)
    {
        $joins = $builder->getJoins();
        return sprintf('SELECT %s FROM "%s" %s %s%s%s%s%s;',
            empty($joins) ? $this->makeSimpleColumns($builder->getColumns()) : $this->makeFullColumns($builder->getColumns()),
            $builder->getBaseTable(),
            empty($joins) ? '' : $this->makeJoin($builder->getJoins()),
            empty($joins) ? $this->makeSimpleConditions($builder->getConditions(), $builder->getRelation()) : $this->makeFullConditions($builder->getConditions(), $builder->getRelation()),
            empty($joins) ? $this->makeSimpleGrouping($builder->getGrouping()) : $this->makeFullGrouping($builder->getGrouping()),
            empty($joins) ? $this->makeSimpleHaving($builder->getHavingCondition(), $builder->getRelation()) : $this->makeFullHaving($builder->getHavingCondition(), $builder->getRelation()),
            empty($joins) ? $this->makeSimpleOrdering($builder->getOrdering()) : $this->makeFullOrdering($builder->getOrdering()),
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

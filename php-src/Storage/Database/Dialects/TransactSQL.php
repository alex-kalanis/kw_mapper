<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Class TransactSQL
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * Create queries for TransactSQL - MSSQL, MS Azure and Sybase servers
 */
class TransactSQL extends ADialect
{
    /**
     * @param QueryBuilder $builder
     * @return string
     * @link https://docs.microsoft.com/en-us/sql/t-sql/statements/insert-transact-sql?view=sql-server-ver15
     */
    public function insert(QueryBuilder $builder)
    {
        return sprintf('INSERT INTO %s SET %s;',
            $builder->getBaseTable(),
            $this->makeProperty($builder->getProperties())
        );
    }

    /**
     * @param QueryBuilder $builder
     * @return string
     * @link https://docs.microsoft.com/en-us/sql/t-sql/queries/select-transact-sql?view=sql-server-ver15
     */
    public function select(QueryBuilder $builder)
    {
        return sprintf('SELECT %s %s FROM %s %s %s%s%s%s%s;',
            $this->makeLimit($builder->getLimit()),
            $this->makeColumns($builder->getColumns()),
            $builder->getBaseTable(),
            $this->makeJoin($builder->getJoins()),
            $this->makeConditions($builder->getConditions(), $builder->getRelation()),
            $this->makeGrouping($builder->getGrouping()),
            $this->makeHaving($builder->getHavingCondition(), $builder->getRelation()),
            $this->makeOrdering($builder->getOrdering()),
            $this->makeOffset($builder->getOffset())
        );
    }

    /**
     * @param QueryBuilder $builder
     * @return string
     * @link https://docs.microsoft.com/en-us/sql/t-sql/queries/update-transact-sql?view=sql-server-ver15
     */
    public function update(QueryBuilder $builder)
    {
        return sprintf('UPDATE %s %s SET %s%s;',
            $this->makeLimit($builder->getLimit()),
            $builder->getBaseTable(),
            $this->makeProperty($builder->getProperties()),
            $this->makeConditions($builder->getConditions(), $builder->getRelation())
        );
    }

    /**
     * @param QueryBuilder $builder
     * @return string
     * @link https://docs.microsoft.com/en-us/sql/t-sql/statements/delete-transact-sql?view=sql-server-ver15
     */
    public function delete(QueryBuilder $builder)
    {
        return sprintf('DELETE %s FROM %s%s;',
            $this->makeLimit($builder->getLimit()),
            $builder->getBaseTable(),
            $this->makeConditions($builder->getConditions(), $builder->getRelation())
        );
    }

    public function describe(QueryBuilder $builder)
    {
        return sprintf('SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = \'%s\';', $builder->getBaseTable() );
    }

    protected function makeLimit(?int $limit): string
    {
        return is_null($limit) ? '' : sprintf('TOP(%d)', $limit);
    }

    protected function makeOffset(?int $offset): string
    {
        return is_null($offset) ? '' : sprintf(' OFFSET %d ', $offset);
    }

    public function availableJoins(): array
    {
        return [
            IQueryBuilder::JOIN_INNER,
            IQueryBuilder::JOIN_LEFT,
            IQueryBuilder::JOIN_RIGHT,
            IQueryBuilder::JOIN_FULL,
            IQueryBuilder::JOIN_CROSS,
        ];
    }
}

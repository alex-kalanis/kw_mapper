<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Class TransactSQL
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * Create queries for TransactSQL - MSSQL, MS Azure and Sybase servers
 */
class TransactSQL extends ADialect
{
    use TQuotationDialect;

    /**
     * @param QueryBuilder $builder
     * @throws MapperException
     * @return string
     * @link https://docs.microsoft.com/en-us/sql/t-sql/statements/insert-transact-sql?view=sql-server-ver15
     */
    public function insert(QueryBuilder $builder)
    {
        return sprintf('INSERT INTO "%s" (%s) VALUES (%s);',
            $builder->getBaseTable(),
            $this->makeSimplePropertyList($builder->getProperties()),
            $this->makePropertyEntries($builder->getProperties())
        );
    }

    /**
     * @param QueryBuilder $builder
     * @return string
     * @link https://docs.microsoft.com/en-us/sql/t-sql/queries/select-transact-sql?view=sql-server-ver15
     */
    public function select(QueryBuilder $builder)
    {
        $joins = $builder->getJoins();
        return sprintf('SELECT %s %s FROM "%s" %s %s%s%s%s%s;',
            $this->makeLimit($builder->getLimit()),
            empty($joins) ? $this->makeSimpleColumns($builder->getColumns()) : $this->makeFullColumns($builder->getColumns()),
            $builder->getBaseTable(),
            empty($joins) ? '' : $this->makeJoin($builder->getJoins()),
            empty($joins) ? $this->makeSimpleConditions($builder->getConditions(), $builder->getRelation()) : $this->makeFullConditions($builder->getConditions(), $builder->getRelation()),
            empty($joins) ? $this->makeSimpleGrouping($builder->getGrouping()) : $this->makeFullGrouping($builder->getGrouping()),
            empty($joins) ? $this->makeSimpleHaving($builder->getHavingCondition(), $builder->getRelation()) : $this->makeFullHaving($builder->getHavingCondition(), $builder->getRelation()),
            empty($joins) ? $this->makeSimpleOrdering($builder->getOrdering()) : $this->makeFullOrdering($builder->getOrdering()),
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
        return sprintf('UPDATE %s "%s" SET %s%s;',
            $this->makeLimit($builder->getLimit()),
            $builder->getBaseTable(),
            $this->makeProperty($builder->getProperties()),
            $this->makeSimpleConditions($builder->getConditions(), $builder->getRelation())
        );
    }

    /**
     * @param QueryBuilder $builder
     * @return string
     * @link https://docs.microsoft.com/en-us/sql/t-sql/statements/delete-transact-sql?view=sql-server-ver15
     */
    public function delete(QueryBuilder $builder)
    {
        return sprintf('DELETE %s FROM "%s"%s;',
            $this->makeLimit($builder->getLimit()),
            $builder->getBaseTable(),
            $this->makeSimpleConditions($builder->getConditions(), $builder->getRelation())
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

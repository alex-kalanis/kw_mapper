<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Class PostgreSQL
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * Create queries for PostgreSQL servers
 */
class PostgreSQL extends ADialect
{
    public function insert(QueryBuilder $builder)
    {
        return sprintf('INSERT INTO %s (%s) VALUES (%s);',
            $builder->getBaseTable(),
            $this->makePropertyList($builder->getProperties()),
            $this->makePropertyEntries($builder->getProperties())
        );
    }

    public function select(QueryBuilder $builder)
    {
        return sprintf('SELECT %s FROM %s %s %s%s%s%s;',
            $this->makeColumns($builder->getColumns()),
            $builder->getBaseTable(),
            $this->makeJoin($builder->getJoins()),
            $this->makeConditions($builder->getConditions(), $builder->getRelation()),
            $this->makeGrouping($builder->getGrouping()),
            $this->makeOrdering($builder->getOrdering()),
            $this->makeLimits($builder->getLimit(), $builder->getOffset())
        );
    }

    public function update(QueryBuilder $builder)
    {
        return sprintf('UPDATE %s SET %s%s;',
            $builder->getBaseTable(),
            $this->makeProperty($builder->getProperties()),
            $this->makeConditions($builder->getConditions(), $builder->getRelation())
        );
    }

    public function delete(QueryBuilder $builder)
    {
        return sprintf('DELETE FROM %s%s;',
            $builder->getBaseTable(),
            $this->makeConditions($builder->getConditions(), $builder->getRelation())
        );
    }

    public function describe(QueryBuilder $builder)
    {
        return sprintf('SELECT table_name, column_name, data_type FROM information_schema.columns WHERE table_name = \'%s\';', $builder->getBaseTable() );
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

    public function singlePropertyListed(QueryBuilder\Property $column): string
    {
        return strval($column->getColumnName());
    }

    public function availableJoins(): array
    {
        return [
            IQueryBuilder::JOIN_BASIC,
            IQueryBuilder::JOIN_INNER,
            IQueryBuilder::JOIN_LEFT,
            IQueryBuilder::JOIN_RIGHT,
            IQueryBuilder::JOIN_FULL,
        ];
    }
}

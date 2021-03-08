<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Class MySQL
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * Create queries for MySQL / MariaDB / Percona servers
 */
class MySQL extends AEscapedDialect
{
    public function insert(QueryBuilder $builder): string
    {
        return sprintf('INSERT INTO `%s` SET %s;',
            $builder->getBaseTable(),
            $this->makeProperty($builder->getProperties())
        );
    }

    public function select(QueryBuilder $builder): string
    {
        return sprintf('SELECT %s FROM `%s` %s %s%s%s%s;',
            $this->makeColumns($builder->getColumns()),
            $builder->getBaseTable(),
            $this->makeJoin($builder->getJoins()),
            $this->makeConditions($builder->getConditions(), $builder->getRelation()),
            $this->makeGrouping($builder->getGrouping()),
            $this->makeOrdering($builder->getOrdering()),
            $this->makeLimits($builder->getLimit(), $builder->getOffset())
        );
    }

    public function update(QueryBuilder $builder): string
    {
        return sprintf('UPDATE `%s` SET %s WHERE %s%s;',
            $builder->getBaseTable(),
            $this->makeProperty($builder->getProperties()),
            $this->makeConditions($builder->getConditions(), $builder->getRelation()),
            $this->makeLimits($builder->getOffset(), null)
        );
    }

    public function delete(QueryBuilder $builder): string
    {
        return sprintf('DELETE FROM `%s` WHERE %s%s;',
            $builder->getBaseTable(),
            $this->makeConditions($builder->getConditions(), $builder->getRelation()),
            $this->makeLimits($builder->getLimit(), null)
        );
    }

    public function describe(QueryBuilder $builder): string
    {
        return sprintf('DESCRIBE `%s`;', $builder->getBaseTable() );
    }

    protected function makeLimits(?int $limit, ?int $offset): string
    {
        return is_null($limit)
            ? ''
            : (is_null($offset)
                ? sprintf(' LIMIT %d', $limit)
                : sprintf(' LIMIT %d,%d', $offset, $limit)
            )
        ;
    }

    public function availableJoins(): array
    {
        return [
            IQueryBuilder::JOIN_BASIC,
            IQueryBuilder::JOIN_INNER,
            IQueryBuilder::JOIN_CROSS,
            IQueryBuilder::JOIN_LEFT,
            IQueryBuilder::JOIN_RIGHT,
        ];
    }
}

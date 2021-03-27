<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Class Oracle
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * Create queries for Oracle servers
 * WTF with limits - use Oracle12c database or you must write your own dialect
 * Also for the similar purposes you cannot limit amount of deleted or updated rows
 * @link https://www.databasestar.com/limit-the-number-of-rows-in-oracle/
 */
class Oracle extends AEscapedDialect
{
    public function insert(QueryBuilder $builder): string
    {
        return sprintf('INSERT INTO `%s` (%s) VALUES (%s);',
            $builder->getBaseTable(),
            $this->makePropertyList($builder->getProperties()),
            $this->makePropertyEntries($builder->getProperties())
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
        return sprintf('UPDATE `%s` SET %s%s;',
            $builder->getBaseTable(),
            $this->makeProperty($builder->getProperties()),
            $this->makeConditions($builder->getConditions(), $builder->getRelation())
        );
    }

    public function delete(QueryBuilder $builder): string
    {
        return sprintf('DELETE FROM `%s`%s;',
            $builder->getBaseTable(),
            $this->makeConditions($builder->getConditions(), $builder->getRelation())
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
            : sprintf(' OFFSET %d ROWS FETCH NEXT %d ROWS ONLY', intval($offset), $limit)
        ;
    }

    public function singlePropertyListed(QueryBuilder\Property $column): string
    {
        return sprintf('`%s`', $column->getColumnName());
    }

    public function availableJoins(): array
    {
        return [
            IQueryBuilder::JOIN_BASIC,
            IQueryBuilder::JOIN_INNER,
            IQueryBuilder::JOIN_CROSS,
            IQueryBuilder::JOIN_LEFT,
            IQueryBuilder::JOIN_RIGHT,
            IQueryBuilder::JOIN_FULL,
        ];
    }
}

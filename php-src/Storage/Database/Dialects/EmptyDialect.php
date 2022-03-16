<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Class EmptyDialect
 * @package kalanis\kw_mapper\Search\Connector\Ldap
 * Build no queries
 */
class EmptyDialect extends ADialect
{
    public function insert(QueryBuilder $builder)
    {
        return '';
    }

    public function select(QueryBuilder $builder)
    {
        return '';
    }

    public function update(QueryBuilder $builder)
    {
        return '';
    }

    public function delete(QueryBuilder $builder)
    {
        return '';
    }

    public function describe(QueryBuilder $builder)
    {
        return '';
    }

    public function availableJoins(): array
    {
        return [];
    }
}

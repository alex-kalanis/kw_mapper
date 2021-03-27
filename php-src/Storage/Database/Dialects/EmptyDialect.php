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
    public function insert(QueryBuilder $builder): string
    {
        return '';
    }

    public function select(QueryBuilder $builder): string
    {
        return '';
    }

    public function update(QueryBuilder $builder): string
    {
        return '';
    }

    public function delete(QueryBuilder $builder): string
    {
        return '';
    }

    public function describe(QueryBuilder $builder): string
    {
        return '';
    }

    public function availableJoins(): array
    {
        return [];
    }
}

<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Class MySQL
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * Create queries for MySQL / MariaDB / Percona servers
 */
class MySQL extends ADialect
{
    use TEscapedDialect;

    public function insert(QueryBuilder $builder)
    {
        return sprintf('INSERT INTO `%s` SET %s;',
            $builder->getBaseTable(),
            $this->makeProperty($builder->getProperties())
        );
    }

    public function select(QueryBuilder $builder)
    {
        return sprintf('SELECT %s FROM `%s` %s %s%s%s%s%s;',
            $this->makeFullColumns($builder->getColumns()),
            $builder->getBaseTable(),
            $this->makeJoin($builder->getJoins()),
            $this->makeFullConditions($builder->getConditions(), $builder->getRelation()),
            $this->makeFullGrouping($builder->getGrouping()),
            $this->makeFullHaving($builder->getHavingCondition(), $builder->getRelation()),
            $this->makeFullOrdering($builder->getOrdering()),
            $this->makeLimits($builder->getLimit(), $builder->getOffset())
        );
    }

    public function update(QueryBuilder $builder)
    {
        return sprintf('UPDATE `%s` SET %s%s%s;',
            $builder->getBaseTable(),
            $this->makeProperty($builder->getProperties()),
            $this->makeFullConditions($builder->getConditions(), $builder->getRelation()),
            $this->makeLimits($builder->getOffset(), null)
        );
    }

    public function delete(QueryBuilder $builder)
    {
        return sprintf('DELETE FROM `%s`%s%s;',
            $builder->getBaseTable(),
            $this->makeFullConditions($builder->getConditions(), $builder->getRelation()),
            $this->makeLimits($builder->getLimit(), null)
        );
    }

    public function describe(QueryBuilder $builder)
    {
        return sprintf('DESCRIBE `%s`;', $builder->getBaseTable() );
    }

    /**
     * @param string $operation
     * @throws MapperException
     * @return string
     * @codeCoverageIgnore too many options
     */
    public function translateOperation(string $operation): string
    {
        switch ($operation) {
            case IQueryBuilder::OPERATION_NULL:
                return 'IS NULL';
            case IQueryBuilder::OPERATION_NNULL:
                return 'IS NOT NULL';
            case IQueryBuilder::OPERATION_EQ:
                return '<=>';
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
                throw new MapperException(sprintf('Unknown operation *%s*', $operation));
        }
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

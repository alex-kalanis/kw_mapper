<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;
use MongoDB\Driver;


/**
 * Class MongoDb
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * Create queries for MongoDB servers
 * @codeCoverageIgnore how to test objects instead of strings?
 */
class MongoDb extends ADialect
{
    use TTranslate;

    public function insert(QueryBuilder $builder)
    {
        $write = new Driver\BulkWrite();
        $write->insert($this->propertyArray($builder));
        return $write;
    }

    public function select(QueryBuilder $builder)
    {
        $options = [];
        if (!empty($builder->getOrdering())) {
            $options['sort'] = $this->order($builder->getOrdering());
        }
        if (!is_null($builder->getOffset())) {
            $options['skip'] = $builder->getOffset();
        }
        if (!is_null($builder->getLimit())) {
            $options['limit'] = $builder->getLimit();
        }
        return new Driver\Query($this->filterArray($builder), $options);
    }

    public function update(QueryBuilder $builder)
    {
        $write = new Driver\BulkWrite();
        $write->update($this->filterArray($builder), $this->propertyArray($builder));
        return $write;
    }

    public function delete(QueryBuilder $builder)
    {
        $write = new Driver\BulkWrite();
        $write->delete($this->filterArray($builder));
        return $write;
    }

    public function describe(QueryBuilder $builder)
    {
        return '';
    }

    /**
     * @param QueryBuilder $builder
     * @return string[]|int[]|float[]
     */
    public function propertyArray(QueryBuilder $builder): array
    {
        $result = [];
        $values = $builder->getParams();
        foreach ($builder->getProperties() as $column) {
            $result[$column->getColumnName()] = $values[$column->getColumnKey()];
        }
        return $result;
    }

    /**
     * @param QueryBuilder $builder
     * @throws MapperException
     * @return array<string, array<array<string, int|string|float|array<int, array<int|string|float>>>>>
     */
    public function filterArray(QueryBuilder $builder): array
    {
        $result = [];
        $values = $builder->getParams();
        foreach ($builder->getConditions() as $condition) {
            if ($condition->isRaw()) {
                $result = array_merge($result, (array) $condition->getRaw());
            } else {
                $result[$condition->getColumnName()] = $this->operation($condition, $values);
            }
        }
        $relation = (IQueryBuilder::RELATION_AND == $builder->getRelation()) ? '$and' : '$or';
        return [$relation => $result];
    }

    /**
     * @param QueryBuilder\Order[] $ordering
     * @return array<string|int, int>
     */
    public function order(array $ordering): array
    {
        $columns = [];
        foreach ($ordering as $column) {
            $columns[$column->getColumnName()] = (IQueryBuilder::ORDER_ASC == $column->getDirection() ? 1 : -1 );
        }
        return $columns;
    }

    public function availableJoins(): array
    {
        return [];
    }

    /**
     * @param QueryBuilder\Condition $condition
     * @param array<int|string> $values
     * @throws MapperException
     * @return array<string, int|string|float|array<int, array<int|string|float>>>
     */
    protected function operation(QueryBuilder\Condition $condition, array &$values): array
    {
        $columnKey = strval($condition->getColumnKey());
        switch ($condition->getOperation()) {
//            case IQueryBuilder::OPERATION_NULL:
//                return 'IS NULL';
//            case IQueryBuilder::OPERATION_NNULL:
//                return 'IS NOT NULL';
            case IQueryBuilder::OPERATION_EQ:
                return ['$eq' => $values[$columnKey]];
            case IQueryBuilder::OPERATION_NEQ:
                return ['$ne' => $values[$columnKey]];
            case IQueryBuilder::OPERATION_GT:
                return ['$gt' => $values[$columnKey]];
            case IQueryBuilder::OPERATION_GTE:
                return ['$gte' => $values[$columnKey]];
            case IQueryBuilder::OPERATION_LT:
                return ['$le' => $values[$columnKey]];
            case IQueryBuilder::OPERATION_LTE:
                return ['$lte' => $values[$columnKey]];
//            case IQueryBuilder::OPERATION_LIKE:
//                return 'LIKE';
//            case IQueryBuilder::OPERATION_NLIKE:
//                return 'NOT LIKE';
            case IQueryBuilder::OPERATION_REXP:
                return ['$regex' => $values[$columnKey]];
            case IQueryBuilder::OPERATION_IN:
                return ['$in' => [$this->notEmptyArray($values[$columnKey])]];
            case IQueryBuilder::OPERATION_NIN:
                return ['$nin' => [$this->notEmptyArray($values[$columnKey])]];
            default:
                throw new MapperException(sprintf('Unknown operation *%s*', $condition->getOperation()));
        }
    }
}

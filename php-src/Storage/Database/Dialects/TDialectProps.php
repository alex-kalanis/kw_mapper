<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Trait TDialectProps
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * To load correct dialect's fillers from tree
 */
trait TDialectProps
{
    /**
     * @param QueryBuilder\Column[] $columns
     * @return string
     */
    public function makeSimpleColumns(array $columns): string
    {
        if (empty($columns)) {
            return $this->selectAllColumns();
        }
        return implode(', ', array_map([$this, 'singleSimpleColumn'], $columns));
    }

    /**
     * @param QueryBuilder\Column[] $columns
     * @return string
     */
    public function makeFullColumns(array $columns): string
    {
        if (empty($columns)) {
            return $this->selectAllColumns();
        }
        return implode(', ', array_map([$this, 'singleFullColumn'], $columns));
    }

    abstract protected function selectAllColumns(): string;

    abstract public function singleSimpleColumn(QueryBuilder\Column $column): string;

    abstract public function singleFullColumn(QueryBuilder\Column $column): string;

    /**
     * @param QueryBuilder\Property[] $properties
     * @return string
     */
    public function makeProperty(array $properties): string
    {
        if (empty($properties)) {
            return $this->selectAllProperties();
        }
        return implode(', ', array_map([$this, 'singleProperty'], $properties));
    }

    abstract protected function selectAllProperties(): string;

    abstract public function singleProperty(QueryBuilder\Property $column): string;

    /**
     * @param QueryBuilder\Property[] $properties
     * @throws MapperException
     * @return string
     */
    public function makeSimplePropertyList(array $properties): string
    {
        if (empty($properties)) {
            throw new MapperException('Empty property list!');
        }
        return implode(', ', array_map([$this, 'singleSimplePropertyListed'], $properties));
    }

    abstract public function singleSimplePropertyListed(QueryBuilder\Property $column): string;

    /**
     * @param QueryBuilder\Property[] $properties
     * @throws MapperException
     * @return string
     */
    public function makeFullPropertyList(array $properties): string
    {
        if (empty($properties)) {
            throw new MapperException('Empty property list!');
        }
        return implode(', ', array_map([$this, 'singleFullPropertyListed'], $properties));
    }

    abstract public function singleFullPropertyListed(QueryBuilder\Property $column): string;

    /**
     * @param QueryBuilder\Property[] $properties
     * @throws MapperException
     * @return string
     */
    public function makePropertyEntries(array $properties): string
    {
        if (empty($properties)) {
            throw new MapperException('Empty property list!');
        }
        return implode(', ', array_map([$this, 'singlePropertyEntry'], $properties));
    }

    abstract public function singlePropertyEntry(QueryBuilder\Property $column): string;

    /**
     * @param QueryBuilder\Condition[] $conditions
     * @param string $relation
     * @return string
     */
    public function makeSimpleConditions(array $conditions, string $relation): string
    {
        if (empty($conditions)) {
            return '';
        }
        return ' WHERE ' . implode(' ' . $relation . ' ', array_map([$this, 'singleSimpleCondition'], $conditions));
    }

    /**
     * @param QueryBuilder\Condition[] $conditions
     * @param string $relation
     * @return string
     */
    public function makeFullConditions(array $conditions, string $relation): string
    {
        if (empty($conditions)) {
            return '';
        }
        return ' WHERE ' . implode(' ' . $relation . ' ', array_map([$this, 'singleFullCondition'], $conditions));
    }

    /**
     * @param QueryBuilder\Condition[] $conditions
     * @param string $relation
     * @return string
     */
    public function makeSimpleHaving(array $conditions, string $relation): string
    {
        if (empty($conditions)) {
            return '';
        }
        return ' HAVING ' . implode(' ' . $relation . ' ', array_map([$this, 'singleSimpleCondition'], $conditions));
    }

    /**
     * @param QueryBuilder\Condition[] $conditions
     * @param string $relation
     * @return string
     */
    public function makeFullHaving(array $conditions, string $relation): string
    {
        if (empty($conditions)) {
            return '';
        }
        return ' HAVING ' . implode(' ' . $relation . ' ', array_map([$this, 'singleFullCondition'], $conditions));
    }

    /**
     * @param QueryBuilder\Condition $condition
     * @throws MapperException
     * @return string
     */
    abstract public function singleSimpleCondition(QueryBuilder\Condition $condition): string;

    /**
     * @param QueryBuilder\Condition $condition
     * @throws MapperException
     * @return string
     */
    abstract public function singleFullCondition(QueryBuilder\Condition $condition): string;

    /**
     * @param QueryBuilder\Order[] $ordering
     * @return string
     */
    public function makeSimpleOrdering(array $ordering): string
    {
        if (empty($ordering)) {
            return '';
        }
        return ' ORDER BY ' . implode(', ', array_map([$this, 'singleSimpleOrder'], $ordering));
    }

    abstract public function singleSimpleOrder(QueryBuilder\Order $order): string;

    /**
     * @param QueryBuilder\Order[] $ordering
     * @return string
     */
    public function makeFullOrdering(array $ordering): string
    {
        if (empty($ordering)) {
            return '';
        }
        return ' ORDER BY ' . implode(', ', array_map([$this, 'singleFullOrder'], $ordering));
    }

    abstract public function singleFullOrder(QueryBuilder\Order $order): string;

    /**
     * @param QueryBuilder\Group[] $groups
     * @return string
     */
    public function makeSimpleGrouping(array $groups): string
    {
        if (empty($groups)) {
            return '';
        }
        return ' GROUP BY ' . implode(', ', array_map([$this, 'singleSimpleGroup'], $groups));
    }

    abstract public function singleSimpleGroup(QueryBuilder\Group $group): string;

    /**
     * @param QueryBuilder\Group[] $groups
     * @return string
     */
    public function makeFullGrouping(array $groups): string
    {
        if (empty($groups)) {
            return '';
        }
        return ' GROUP BY ' . implode(', ', array_map([$this, 'singleFullGroup'], $groups));
    }

    abstract public function singleFullGroup(QueryBuilder\Group $group): string;

    /**
     * @param QueryBuilder\Join[] $join
     * @return string
     */
    public function makeJoin(array $join): string
    {
        if (empty($join)) {
            return '';
        }
        return implode(' ', array_map([$this, 'singleJoin'], $join));
    }

    abstract public function singleJoin(QueryBuilder\Join $join): string;
}

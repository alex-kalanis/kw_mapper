<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Class AEscapedDialect
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * All actions escaped
 */
abstract class AEscapedDialect extends ADialect
{
    public function singleColumn(QueryBuilder\Column $column): string
    {
        $alias = empty($column->getColumnAlias()) ? '' : sprintf(' AS `%s`', $column->getColumnAlias());
        return empty($column->getAggregate())
            ? sprintf('`%s`.`%s` %s',
                $column->getTableName(),
                $column->getColumnName(),
                $alias
            )
            : sprintf('%s(`%s`.`%s`) %s',
                $column->getAggregate(),
                $column->getTableName(),
                $column->getColumnName(),
                $alias
            )
        ;
    }

    public function singleProperty(QueryBuilder\Property $column): string
    {
        return sprintf('`%s`.`%s` = %s',
            $column->getTableName(),
            $column->getColumnName(),
            $column->getColumnKey()
        );
    }

    public function singlePropertyListed(QueryBuilder\Property $column): string
    {
        return sprintf('`%s`.`%s`',
            $column->getTableName(),
            $column->getColumnName()
        );
    }

    /**
     * @param QueryBuilder\Condition $condition
     * @return string
     * @throws MapperException
     */
    public function singleCondition(QueryBuilder\Condition $condition): string
    {
        return sprintf(' `%s`.`%s` %s %s',
            $condition->getTableName(),
            $condition->getColumnName(),
            $this->translateOperation($condition->getOperation()),
            $this->translateKey($condition->getOperation(), $condition->getColumnKey())
        );
    }

    public function singleOrder(QueryBuilder\Order $order): string
    {
        return empty($order->getTableName())
            ? sprintf('`%s` %s', $order->getColumnName(), $order->getDirection() )
            : sprintf('`%s`.`%s` %s', $order->getTableName(), $order->getColumnName(), $order->getDirection() );
    }

    public function singleGroup(QueryBuilder\Group $group): string
    {
        return empty($group->getTableName())
            ? sprintf(' `%s`', $group->getColumnName())
            : sprintf(' `%s`.`%s`',
                $group->getTableName(),
                $group->getColumnName()
            );
    }

    public function singleJoin(QueryBuilder\Join $join): string
    {
        return sprintf(' %s JOIN `%s`%s ON (`%s`.`%s` = `%s`.`%s`)',
            $join->getSide(),
            $join->getNewTableName(),
            empty($join->getTableAlias()) ? '' : sprintf(' AS `%s`', $join->getTableAlias()),
            $join->getKnownTableName(),
            $join->getKnownColumnName(),
            empty($join->getTableAlias()) ? $join->getNewTableName() : $join->getTableAlias(),
            $join->getNewColumnName()
        );
    }
}

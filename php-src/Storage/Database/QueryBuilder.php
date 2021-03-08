<?php

namespace kalanis\kw_mapper\Storage\Database;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Dialects\ADialect;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder as QB;


/**
 * Class QueryBuilder
 * @package kalanis\kw_mapper\Storage\Database
 * Call language dialects to create queries and pass conditions, properties, joins and params as extra parts
 */
class QueryBuilder extends QB
{
    /** @var ADialect */
    protected $dialect = null;

    public function __construct(ADialect $dialect)
    {
        $this->dialect = $dialect;
        parent::__construct();
    }

    /**
     * @param string $joinUnderAlias
     * @param string $addTableName
     * @param string|int $addColumnName
     * @param string $knownTableName
     * @param string|int $knownColumnName
     * @param string $side
     * @param string $tableAlias
     * @throws MapperException
     */
    public function addJoin(string $joinUnderAlias, string $addTableName, $addColumnName, string $knownTableName, $knownColumnName, string $side = '', string $tableAlias = ''): void
    {
        if (!in_array($side, $this->dialect->availableJoins())) {
            throw new MapperException(sprintf('Bad side *%s* to join !', $side));
        }
        $join = clone $this->join;
        $this->joins[] = $join->setData($joinUnderAlias, $addTableName, $addColumnName, $knownTableName, $knownColumnName, $side, $tableAlias);
    }
}

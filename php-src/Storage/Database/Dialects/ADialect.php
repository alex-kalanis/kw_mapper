<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Class ADialect
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * All actions as defined by CRUD - Create, Read, Update, Delete
 *
 * Hints:
 * For testing purposes we just fill prepared data and by that we got query. Implemention details are the problem
 * of dialect of each language. And it's simple to test that. Then result go to the real connection.
 *
 * @todo:
 * -> database operations - table create, table drop, table alter, ...
 *
 * Escaping of params has been determined on following links:
 * @link http://sqlfiddle.com/
 * @link https://sqliteonline.com
 */
abstract class ADialect
{
    /**
     * Create data by properties
     * @param QueryBuilder $builder
     * @return string|object
     * @throws MapperException
     */
    abstract public function insert(QueryBuilder $builder);

    /**
     * Read data described by conditions
     * @param QueryBuilder $builder
     * @return string|object
     * @throws MapperException
     */
    abstract public function select(QueryBuilder $builder);

    /**
     * Update data properties described by conditions
     * @param QueryBuilder $builder
     * @return string|object
     * @throws MapperException
     */
    abstract public function update(QueryBuilder $builder);

    /**
     * Delete data by conditions
     * @param QueryBuilder $builder
     * @return string|object
     * @throws MapperException
     */
    abstract public function delete(QueryBuilder $builder);

    /**
     * Get table structure
     * @param QueryBuilder $builder
     * @return string|object
     * @throws MapperException
     */
    abstract public function describe(QueryBuilder $builder);

    /**
     * Get array of available join operations
     * @return array
     */
    abstract public function availableJoins(): array;

    protected function selectAllColumns(): string
    {
        return '*';
    }

    protected function selectAllProperties(): string
    {
        return '1=1';
    }

    public function singlePropertyEntry(QueryBuilder\Property $column): string
    {
        return $column->getColumnKey();
    }
}

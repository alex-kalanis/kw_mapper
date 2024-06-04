<?php

namespace kalanis\kw_mapper\Search\Connector;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Shared\ForeignKey;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Storage;


/**
 * Class AConnector
 * @package kalanis\kw_mapper\Search
 * Connect real sources into search engine
 */
abstract class AConnector
{
    use Database\TRecordsInJoins;

    protected ARecord $basicRecord;
    protected Storage\Shared\QueryBuilder $queryBuilder;

    /**
     * @param string $table
     * @param string $column
     * @param string|float|int $value
     * @throws MapperException
     * @return $this
     */
    public function notExact(string $table, string $column, $value): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_NEQ,
            $value
        );
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @param string|float|int $value
     * @throws MapperException
     * @return $this
     */
    public function exact(string $table, string $column, $value): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_EQ,
            $value
        );
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @param string|float|int $value
     * @param bool $equals
     * @throws MapperException
     * @return $this
     */
    public function from(string $table, string $column, $value, bool $equals = true): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            $equals ? IQueryBuilder::OPERATION_GTE : IQueryBuilder::OPERATION_GT,
            $value
        );
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @param string|float|int $value
     * @param bool $equals
     * @throws MapperException
     * @return $this
     */
    public function to(string $table, string $column, $value, bool $equals = true): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            $equals ? IQueryBuilder::OPERATION_LTE : IQueryBuilder::OPERATION_LT,
            $value
        );
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @param string $value
     * @throws MapperException
     * @return $this
     */
    public function like(string $table, string $column, $value): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_LIKE,
            $value
        );
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @param string $value
     * @throws MapperException
     * @return $this
     */
    public function notLike(string $table, string $column, $value): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_NLIKE,
            $value
        );
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @param string $pattern
     * @throws MapperException
     * @return $this
     */
    public function regexp(string $table, string $column, string $pattern): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_REXP,
            $pattern
        );
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @param string $min
     * @param string $max
     * @throws MapperException
     * @return $this
     */
    public function between(string $table, string $column, $min, $max): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addCondition($aTable, $this->correctColumn($aTable, $column), IQueryBuilder::OPERATION_GTE, $min);
        $this->getQueryBuilder()->addCondition($aTable, $this->correctColumn($aTable, $column), IQueryBuilder::OPERATION_LTE, $max);
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @throws MapperException
     * @return $this
     */
    public function null(string $table, string $column): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_NULL
        );
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @throws MapperException
     * @return $this
     */
    public function notNull(string $table, string $column): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_NNULL
        );
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @param array<string|int|float> $values
     * @throws MapperException
     * @return $this
     */
    public function in(string $table, string $column, array $values): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_IN,
            $values
        );
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @param array<string|int|float> $values
     * @throws MapperException
     * @return $this
     */
    public function notIn(string $table, string $column, array $values): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_NIN,
            $values
        );
        return $this;
    }

    /**
     * @param string|string[]|callable $operation
     * @param string $prefix
     * @param mixed $value
     * @return $this
     */
    public function raw($operation, string $prefix = '', $value = null): self
    {
        $this->getQueryBuilder()->addRawCondition(
            $operation,
            $prefix,
            $value
        );
        return $this;
    }

    public function useAnd(): self
    {
        $this->getQueryBuilder()->setRelations(IQueryBuilder::RELATION_AND);
        return $this;
    }

    public function useOr(): self
    {
        $this->getQueryBuilder()->setRelations(IQueryBuilder::RELATION_OR);
        return $this;
    }

    public function limit(?int $limit): self
    {
        $this->getQueryBuilder()->setLimit($limit);
        return $this;
    }

    public function offset(?int $offset): self
    {
        $this->getQueryBuilder()->setOffset($offset);
        return $this;
    }

    /**
     * Add ordering by
     * @param string $table
     * @param string $column
     * @param string $direction
     * @throws MapperException
     * @return $this
     */
    public function orderBy(string $table, string $column, string $direction = IQueryBuilder::ORDER_ASC): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addOrderBy($aTable, $this->correctColumn($aTable, $column), $direction);
        return $this;
    }

    /**
     * Add grouping by
     * @param string $table
     * @param string $column
     * @throws MapperException
     * @return $this
     */
    public function groupBy(string $table, string $column): self
    {
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addGroupBy($aTable, $this->correctColumn($aTable, $column));
        return $this;
    }

    /**
     * Add child which will be mounted to results
     * @param string $childAlias
     * @param string $joinType
     * @param string $parentAlias
     * @param string $customAlias
     * @throws MapperException
     * @return $this
     */
    public function child(string $childAlias, string $joinType = IQueryBuilder::JOIN_LEFT, string $parentAlias = '', string $customAlias = ''): self
    {
        // from mapper - children's mapper then there table name
        if (!empty($parentAlias)) {
            $parentLookup = $this->recordLookup($parentAlias);
            if ($parentLookup && $parentLookup->getRecord()) {
                $parentRecord = $parentLookup->getRecord();
            }
        } else {
            $parentRecord = $this->getBasicRecord();
            $parentAlias = $parentRecord->getMapper()->getAlias();
        }
        if (empty($parentRecord)) {
            throw new MapperException(sprintf('Unknown record for parent alias *%s*', $parentAlias));
        }

        /** @var array<string|int, ForeignKey> $parentKeys */
        $parentKeys = $parentRecord->getMapper()->getForeignKeys();
        if (!isset($parentKeys[$childAlias])) {
            throw new MapperException(sprintf('Unknown alias *%s* in mapper for parent *%s*', $childAlias, $parentAlias));
        }

        $parentKey = $parentKeys[$childAlias];
        $parentRelations = $parentRecord->getMapper()->getRelations();
        if (empty($parentRelations[$parentKey->getLocalEntryKey()])) {
            throw new MapperException(sprintf('Unknown relation key *%s* in mapper for parent *%s*', $parentKey->getLocalEntryKey(), $parentAlias));
        }

        $childTableAlias = empty($customAlias) ? $childAlias : $customAlias;
        $childLookup = $this->recordLookup($childTableAlias, $childAlias);
        if (empty($childLookup) || empty($childLookup->getRecord())) {
            // might never happens - part already checked, so it must exists
            // @codeCoverageIgnoreStart
            throw new MapperException(sprintf('Unknown record for child alias *%s*', $childAlias));
        }
        // @codeCoverageIgnoreEnd

        $childRecord = $childLookup->getRecord();
        $childRelations = $childRecord->getMapper()->getRelations();
        if (empty($childRelations[$parentKey->getRemoteEntryKey()])) {
            throw new MapperException(sprintf('Unknown relation key *%s* in mapper for child *%s*', $parentKey->getRemoteEntryKey(), $childAlias));
        }

        if ($parentRecord->getMapper()->getSource() != $childRecord->getMapper()->getSource()) {
            throw new MapperException(sprintf('Parent *%s* and child *%s* must both have the same source', $parentAlias, $childAlias));
        }

        $this->getQueryBuilder()->addJoin(
            $childAlias,
            $childRecord->getMapper()->getAlias(),
            $childRelations[$parentKey->getRemoteEntryKey()],
            $parentAlias,
            $parentRelations[$parentKey->getLocalEntryKey()],
            $joinType,
            $childTableAlias
        );

        return $this;
    }

    /**
     * That child is not set for chosen parent
     * @param string $childAlias
     * @param string $table
     * @param string $column
     * @param string $parentAlias
     * @throws MapperException
     * @return $this
     */
    public function childNotExist(string $childAlias, string $table, string $column, string $parentAlias = ''): self
    {
        $this->child($childAlias, IQueryBuilder::JOIN_LEFT_OUTER, $parentAlias);
        $aTable = $this->correctTable($table);
        $this->getQueryBuilder()->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_NULL
        );
        return $this;
    }

    /**
     * Return count of all records selected by params
     * @throws MapperException
     * @return int
     */
    abstract public function getCount(): int;

    /**
     * Return records
     * @throws MapperException
     * @return ARecord[]
     */
    abstract public function getResults(): array;

    /**
     * @param string $table
     * @throws MapperException
     * @return string
     */
    protected function correctTable(string $table): string
    {
        return empty($table) ? $this->getBasicRecord()->getMapper()->getAlias() : $table ;
    }

    /**
     * @param string $table
     * @param string $column
     * @throws MapperException
     * @return string|int
     */
    protected function correctColumn(string $table, string $column)
    {
        $record = !empty($table) ? $this->recordLookup($table)->getRecord() : $this->getBasicRecord() ;
        if (empty($record)) {
            // @codeCoverageIgnoreStart
            throw new MapperException(sprintf('Unknown relation table *%s*', $table));
        }
        // @codeCoverageIgnoreEnd
        $relations = $record->getMapper()->getRelations();
        if (empty($relations[$column])) {
            throw new MapperException(sprintf('Unknown relation key *%s* in mapper for table *%s*', $column, $table));
        }
        return $relations[$column];
    }

    protected function getQueryBuilder(): Storage\Shared\QueryBuilder
    {
        return $this->queryBuilder;
    }

    protected function getBasicRecord(): ARecord
    {
        return $this->basicRecord;
    }
}

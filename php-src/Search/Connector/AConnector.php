<?php

namespace kalanis\kw_mapper\Search\Connector;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Storage;


/**
 * Class AConnector
 * @package kalanis\kw_mapper\Search
 * Connect real sources into search engine
 */
abstract class AConnector
{
    /** @var ARecord */
    protected $basicRecord = null;
    /** @var ARecord[] */
    protected $records = [];
    /** @var string[][] */
    protected $childTree = [];
    /** @var Storage\Shared\QueryBuilder */
    protected $queryBuilder = null;

    /**
     * @param string $table
     * @param string $column
     * @param string $value
     * @return $this
     * @throws MapperException
     */
    public function notExact(string $table, string $column, $value): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addCondition(
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
     * @param string $value
     * @return $this
     * @throws MapperException
     */
    public function exact(string $table, string $column, $value): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addCondition(
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
     * @param string $value
     * @param bool $equals
     * @return $this
     * @throws MapperException
     */
    public function from(string $table, string $column, $value, bool $equals = true): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addCondition(
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
     * @param string $value
     * @param bool $equals
     * @return $this
     * @throws MapperException
     */
    public function to(string $table, string $column, $value, bool $equals = true): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addCondition(
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
     * @return $this
     * @throws MapperException
     */
    public function like(string $table, string $column, $value): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addCondition(
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
     * @return $this
     * @throws MapperException
     */
    public function notLike(string $table, string $column, $value): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addCondition(
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
     * @return $this
     * @throws MapperException
     */
    public function regexp(string $table, string $column, string $pattern): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addCondition(
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
     * @return $this
     * @throws MapperException
     */
    public function between(string $table, string $column, $min, $max): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addCondition($aTable, $this->correctColumn($aTable, $column), IQueryBuilder::OPERATION_GTE, $min);
        $this->queryBuilder->addCondition($aTable, $this->correctColumn($aTable, $column), IQueryBuilder::OPERATION_LTE, $max);
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @return $this
     * @throws MapperException
     */
    public function null(string $table, string $column): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_NULL
        );
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @return $this
     * @throws MapperException
     */
    public function notNull(string $table, string $column): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_NNULL
        );
        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @param array $values
     * @return $this
     * @throws MapperException
     */
    public function in(string $table, string $column, array $values): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addCondition(
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
     * @param array $values
     * @return $this
     * @throws MapperException
     */
    public function notIn(string $table, string $column, array $values): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_NIN,
            $values
        );
        return $this;
    }

    public function useAnd(): self
    {
        $this->queryBuilder->setRelations(IQueryBuilder::RELATION_AND);
        return $this;
    }

    public function useOr(): self
    {
        $this->queryBuilder->setRelations(IQueryBuilder::RELATION_OR);
        return $this;
    }

    public function limit(?int $limit): self
    {
        $this->queryBuilder->setLimit($limit);
        return $this;
    }

    public function offset(?int $offset): self
    {
        $this->queryBuilder->setOffset($offset);
        return $this;
    }

    /**
     * Add ordering by
     * @param string $table
     * @param string $column
     * @param string $direction
     * @return $this
     * @throws MapperException
     */
    public function orderBy(string $table, string $column, string $direction = IQueryBuilder::ORDER_ASC): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addOrderBy($aTable, $this->correctColumn($aTable, $column), $direction);
        return $this;
    }

    /**
     * Add grouping by
     * @param string $table
     * @param string $column
     * @return $this
     * @throws MapperException
     */
    public function groupBy(string $table, string $column): self
    {
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addGroupBy($aTable, $this->correctColumn($aTable, $column));
        return $this;
    }

    /**
     * Add child which will be mounted to results
     * @param string $childAlias
     * @param string $joinType
     * @param string $parentAlias
     * @param string $customAlias
     * @return $this
     * @throws MapperException
     */
    public function child(string $childAlias, string $joinType = IQueryBuilder::JOIN_LEFT, string $parentAlias = '', string $customAlias = ''): self
    {
        // from mapper - children's mapper then there table name
        $parentRecord = empty($parentAlias) ? $this->basicRecord : $this->recordLookup($parentAlias) ;
        if (empty($parentRecord)) {
            throw new MapperException(sprintf('Unknown mapper for parent alias *%s*', $parentAlias));
        }
        $parentKeys = $parentRecord->getMapper()->getForeignKeys();
        if (!isset($parentKeys[$childAlias])) {
            throw new MapperException(sprintf('Unknown alias *%s* in mapper for parent *%s*', $childAlias, $parentAlias));
        }
        $parentKey = $parentKeys[$childAlias];
        $parentRelations = $parentRecord->getMapper()->getRelations();
        if (empty($parentRelations[$parentKey->getLocalEntryKey()])) {
            throw new MapperException(sprintf('Unknown relation key *%s* in mapper for parent *%s*', $parentKey->getLocalEntryKey(), $parentAlias));
        }

        $childMapper = $this->recordLookup($childAlias, $customAlias);
        if (empty($childMapper)) {
            throw new MapperException(sprintf('Unknown mapper for child alias *%s*', $childAlias));
        }
        $childRelations = $childMapper->getMapper()->getRelations();
        if (empty($childRelations[$parentKey->getRemoteEntryKey()])) {
            throw new MapperException(sprintf('Unknown relation key *%s* in mapper for child *%s*', $parentKey->getRemoteEntryKey(), $childAlias));
        }

        $tableAlias = empty($customAlias) ? $childAlias : $customAlias;
        $knownTableName = empty($parentAlias) ? $parentRecord->getMapper()->getAlias() : $parentAlias ;
        $this->queryBuilder->addJoin(
            $childAlias,
            $childMapper->getMapper()->getAlias(),
            $childRelations[$parentKey->getRemoteEntryKey()],
            $knownTableName,
            $parentRelations[$parentKey->getLocalEntryKey()],
            $joinType,
            $tableAlias
        );

        $this->childTree[$tableAlias] = $this->childTree[$knownTableName] + [$tableAlias => $childAlias];
        return $this;
    }

    /**
     * That child is not set for chosen parent
     * @param string $childAlias
     * @param string $table
     * @param string $column
     * @param string $parentAlias
     * @return $this
     * @throws MapperException
     */
    public function childNotExist(string $childAlias, string $table, string $column, string $parentAlias = ''): self
    {
        $this->child($childAlias, IQueryBuilder::JOIN_LEFT_OUTER, $parentAlias);
        $aTable = $this->correctTable($table);
        $this->queryBuilder->addCondition(
            $aTable,
            $this->correctColumn($aTable, $column),
            IQueryBuilder::OPERATION_NULL
        );
        return $this;
    }

    /**
     * Returns tree for accessing the child
     * @param string $childAlias
     * @return string[]
     * @throws MapperException
     */
    public function childTree(string $childAlias): array
    {
        if (!isset($this->childTree[$childAlias])) {
            throw new MapperException(sprintf('Unknown alias *%s* in child tree.', $childAlias));
        }
        return $this->childTree[$childAlias];
    }

    /**
     * Return count of all records selected by params
     * @return int
     * @throws MapperException
     */
    abstract public function getCount(): int;

    /**
     * Return records
     * @return ARecord[]
     * @throws MapperException
     */
    abstract public function getResults(): array;


    protected function correctTable(string $table): string
    {
        return empty($table) ? $this->basicRecord->getMapper()->getAlias() : $table ;
    }

    /**
     * @param string $table
     * @param string $column
     * @return string|int
     * @throws MapperException
     */
    protected function correctColumn(string $table, string $column)
    {
        $record = $this->recordLookup($table);
        $relations = $record->getMapper()->getRelations();
        if (empty($relations[$column])) {
            throw new MapperException(sprintf('Unknown relation key *%s* in mapper for table *%s*', $column, $table));
        }
        return $relations[$column];
    }

    protected function recordLookup(string $wantedAlias, string $customAlias = ''): ?ARecord
    {
        $key = empty($customAlias) ? $wantedAlias : $customAlias ;
        if (isset($this->records[$key])) {
            return $this->records[$key];
        }
        foreach ($this->records as $record) {
            $foreignKeys = $record->getMapper()->getForeignKeys();
            if (isset($foreignKeys[$wantedAlias])) {
                $recordClassName = $foreignKeys[$wantedAlias]->getRemoteRecord();
                $this->records[$key] = new $recordClassName();
                return $this->records[$key];
            }
        }
        return null;
    }

}

<?php

namespace kalanis\kw_mapper\Search\Connector;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Storage;


/**
 * Class Database
 * @package kalanis\kw_mapper\Search
 * Connect database as datasource
 */
class Database extends AConnector
{
    /** @var Storage\Database\ASQL */
    protected $database = null;
    /** @var Storage\Database\Dialects\ADialect */
    protected $dialect = null;
    /** @var Database\Filler */
    protected $filler = null;

    /**
     * @param ARecord $record
     * @throws MapperException
     */
    public function __construct(ARecord $record, ?Storage\Shared\QueryBuilder $builder = null)
    {
        $this->basicRecord = $record;
        $this->initRecordLookup($record);
        $config = Storage\Database\ConfigStorage::getInstance()->getConfig($record->getMapper()->getSource());
        $this->database = Storage\Database\DatabaseSingleton::getInstance()->getDatabase($config);
        $this->dialect = Storage\Database\Dialects\Factory::getInstance()->getDialectClass($this->database->languageDialect());
        $this->queryBuilder = $builder ?: new Storage\Database\QueryBuilder($this->dialect);
        $this->queryBuilder->setBaseTable($record->getMapper()->getAlias());
        $this->filler = new Database\Filler($this->basicRecord);
    }

    public function getCount(): int
    {
        $this->queryBuilder->clearColumns();
        $relations = $this->basicRecord->getMapper()->getRelations();
        if (empty($this->basicRecord->getMapper()->getPrimaryKeys())) {
            // @codeCoverageIgnoreStart
            // no PKs in table
            $this->queryBuilder->addColumn($this->basicRecord->getMapper()->getAlias(), strval(reset($relations)), 'count', IQueryBuilder::AGGREGATE_COUNT);
            // @codeCoverageIgnoreEnd
        } else {
            $pks = $this->basicRecord->getMapper()->getPrimaryKeys();
            $this->queryBuilder->addColumn($this->basicRecord->getMapper()->getAlias(), strval($relations[strval(reset($pks))]), 'count', IQueryBuilder::AGGREGATE_COUNT);
        }

        $lines = $this->database->query(strval($this->dialect->select($this->queryBuilder)), array_filter($this->queryBuilder->getParams(), [$this, 'filterNullValues']));
        if (empty($lines) || !is_iterable($lines)) {
            // @codeCoverageIgnoreStart
            // only when something horribly fails
            return 0;
        }
        // @codeCoverageIgnoreEnd
        $line = reset($lines);
        return intval(reset($line));
    }

    public function getResults(): array
    {
        $this->queryBuilder->clearColumns();
        $this->filler->initTreeSolver($this->recordsInJoin);
        foreach ($this->filler->getColumns($this->queryBuilder->getJoins()) as list($table, $column, $alias)) {
            $this->queryBuilder->addColumn(strval($table), strval($column), strval($alias));
        }

        $select = strval($this->dialect->select($this->queryBuilder));
//print_r(str_split($select, 100));
        $rows = $this->database->query($select, array_filter($this->queryBuilder->getParams(), [$this, 'filterNullValues']));
        if (empty($rows) || !is_iterable($rows)) {
            return [];
        }
//print_r($rows);

        return $this->filler->fillResults($rows, $this);
    }

    /**
     * When it contains null value, then it's empty for usage - because Null has direct representation in query builder as operation -> no need to pass data
     * @param mixed $value
     * @return bool
     */
    public function filterNullValues($value): bool
    {
        return !is_null($value);
    }
}

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
    /** @var Storage\Database\ADatabase */
    protected $database = null;
    /** @var Storage\Database\Dialects\ADialect */
    protected $dialect = null;
    /** @var Database\Filler */
    protected $filler = null;

    /**
     * @param ARecord $record
     * @throws MapperException
     */
    public function __construct(ARecord $record)
    {
        $this->basicRecord = $record;
        $alias = $record->getMapper()->getAlias();
        $this->records[$alias] = $record;
        $this->childTree[$alias] = [$alias => $alias];
        $config = Storage\Database\ConfigStorage::getInstance()->getConfig($record->getMapper()->getSource());
        $this->database = Storage\Database\DatabaseSingleton::getInstance()->getDatabase($config);
        $this->dialect = Storage\Database\Dialects\Factory::getInstance()->getDialectClass($this->database->languageDialect());
        $this->queryBuilder = new Storage\Database\QueryBuilder($this->dialect);
        $this->queryBuilder->setBaseTable($alias);
        $this->filler = new Database\Filler($record);
    }

    public function getCount(): int
    {
        $this->queryBuilder->clearColumns();
        if (empty($this->basicRecord->getMapper()->getPrimaryKeys())) {
            $relations = $this->basicRecord->getMapper()->getRelations();
            $this->queryBuilder->addColumn($this->basicRecord->getMapper()->getAlias(), reset($relations), 'count', IQueryBuilder::AGGREGATE_COUNT);
        } else {
            $pks = $this->basicRecord->getMapper()->getPrimaryKeys();
            $this->queryBuilder->addColumn($this->basicRecord->getMapper()->getAlias(), reset($pks), 'count', IQueryBuilder::AGGREGATE_COUNT);
        }

        $lines = $this->database->query($this->dialect->select($this->queryBuilder), $this->queryBuilder->getParams());
        if (empty($lines) || !is_iterable($lines)) {
            return 0;
        }
        $line = reset($lines);
        return intval(reset($line));
    }

    public function getResults(): array
    {
        $this->queryBuilder->clearColumns();
        foreach ($this->filler->getColumns($this->records, $this->queryBuilder->getJoins()) as list($table, $column, $alias)) {
            $this->queryBuilder->addColumn($table, $column, $alias);
        }

        $select = $this->dialect->select($this->queryBuilder);
//print_r(str_split($select, 100));
        $rows = $this->database->query($select, $this->queryBuilder->getParams());
        if (empty($rows) || !is_iterable($rows)) {
            return [];
        }
//print_r($rows);

        return $this->filler->fillResults($this->records, $this->queryBuilder->getJoins(), $rows, $this);
    }
}

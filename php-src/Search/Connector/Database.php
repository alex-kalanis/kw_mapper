<?php

namespace kalanis\kw_mapper\Search\Connector;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Shared\TFilterNulls;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Storage;


/**
 * Class Database
 * @package kalanis\kw_mapper\Search
 * Connect database as datasource
 */
class Database extends AConnector
{
    use TFilterNulls;

    protected Storage\Database\ASQL $database;
    protected Storage\Database\Dialects\ADialect $dialect;
    protected Database\Filler $filler;

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
        $countQueryBuilder = clone $this->queryBuilder;
        $countQueryBuilder->clearColumns();
        $countQueryBuilder->clearOrdering();
        $relations = $this->basicRecord->getMapper()->getRelations();
        if (empty($this->basicRecord->getMapper()->getPrimaryKeys())) {
            // @codeCoverageIgnoreStart
            // no PKs in table
            $countQueryBuilder->addColumn($this->basicRecord->getMapper()->getAlias(), strval(reset($relations)), 'count', IQueryBuilder::AGGREGATE_COUNT);
            // @codeCoverageIgnoreEnd
        } else {
            $pks = $this->basicRecord->getMapper()->getPrimaryKeys();
            $countQueryBuilder->addColumn($this->basicRecord->getMapper()->getAlias(), strval($relations[strval(reset($pks))]), 'count', IQueryBuilder::AGGREGATE_COUNT);
        }

        $lines = $this->database->query(strval($this->dialect->select($countQueryBuilder)), array_filter($countQueryBuilder->getParams(), [$this, 'filterNullValues']));
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
        $resultQueryBuilder = clone $this->queryBuilder;
        $resultQueryBuilder->clearColumns();
        $this->filler->initTreeSolver($this->recordsInJoin);
        foreach ($this->filler->getColumns($resultQueryBuilder->getJoins()) as list($table, $column, $alias)) {
            $resultQueryBuilder->addColumn(strval($table), strval($column), strval($alias));
        }
        if (empty($resultQueryBuilder->getOrdering())) {
            $basicRelations = $this->basicRecord->getMapper()->getRelations();
            foreach ($this->basicRecord->getMapper()->getPrimaryKeys() as $primaryKey) {
                if (isset($basicRelations[$primaryKey])) {
                    $resultQueryBuilder->addOrderBy($resultQueryBuilder->getBaseTable(), $basicRelations[$primaryKey], IQueryBuilder::ORDER_ASC);
                }
            }
        }

        $select = strval($this->dialect->select($resultQueryBuilder));
//print_r(str_split($select, 100));
        $rows = $this->database->query($select, array_filter($resultQueryBuilder->getParams(), [$this, 'filterNullValues']));
        if (empty($rows) || !is_iterable($rows)) {
            return [];
        }
//print_r($rows);

        return $this->filler->fillResults($rows, $this);
    }
}

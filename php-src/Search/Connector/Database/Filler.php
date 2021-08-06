<?php

namespace kalanis\kw_mapper\Search\Connector\Database;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\TFill;
use kalanis\kw_mapper\Search\Connector;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder\Join;


/**
 * Class Filler
 * @package kalanis\kw_mapper\Search\Connector\Database
 * Filling both columns and Records
 */
class Filler
{
    use TFill;

    protected $hashDelimiter = "--::\e::--";
    protected $columnDelimiter = "____";
    protected $record = null;
    private $fromDatabase = false;

    public function __construct(ARecord $record)
    {
        $this->record = $record;
    }

    /**
     * @param ARecord[] $records
     * @param Join[] $joins
     * @return string[][]
     */
    public function getColumns(array &$records, array $joins): array
    {
        $used = [];
        $columns = [];
        $join = $this->orderJoinsColumns($joins);
        foreach ($records as $alias => &$record) {
            if (in_array($alias, $used)) { // if they came here more than once
                continue;
            }
            foreach ($record->getMapper()->getRelations() as $relation) {
                if (!empty($join[$alias])) {
                    $columns[] = [$join[$alias], $relation, implode($this->columnDelimiter, [$join[$alias], $relation])];
                } else {
                    $columns[] = [$alias, $relation, implode($this->columnDelimiter, [$alias, $relation])];
                }
            }
            $used[] = $alias;
        }
        return $columns;
    }

    /**
     * @param Join[] $joins
     * @return string[]
     */
    protected function orderJoinsColumns(array &$joins): array
    {
        $return = [];
        foreach ($joins as &$join) {
            $return[$join->getJoinUnderAlias()] = $join->getTableAlias();
        }
        return $return;
    }

    /**
     * Filling results to the tree of records
     * Records came as table, must put them into the tree
     * @param ARecord[] $records
     * @param iterable $rows
     * @param Join[] $joins
     * @param mixed $parent
     * @return ARecord[]
     * @throws MapperException
     *
     * Three tables:
     * first with processed records; the keys are the hashes
     * second, aerial what say what table is where;
     *      first-level is the same as original records and contains sub-table,
     *      second-level has table name as key and hash as value
     * third which tells what is parent
     */
    public function fillResults(array &$records, array $joins, iterable $rows, $parent = null): array
    {
        if ($parent) {
            $this->setAsFromDatabase($parent);
        }
        $soloRecords = [];
        $whatTablesInRow = [];

        // load and translate data from database
        foreach ($rows as $rowId => &$row) {
            $byTables = $this->splitByTables($row);
            foreach ($byTables as $table => &$columns) {
                $hash = md5(implode($this->hashDelimiter, $columns));
                if (!isset($soloRecords[$hash])) { // new one
                    if (!isset($records[$table])) {
                        throw new MapperException(sprintf('Alias of table *%s* cannot been found within available records', $table));
                    }
                    $soloRecords[$hash] = $this->fillTableRecord(clone $records[$table], $columns);
                }
                $whatTablesInRow[$rowId][$table] = $hash;
            }
        }

        $results = [];
        $tableHasChildren = [];
        $joinTables = $this->fillJoins($joins);

        // now we have complete necessary data
        foreach ($whatTablesInRow as &$row) {
            foreach ($row as $table => &$hash) {
                if (empty($joinTables[$table])) { // nothing as parent
                    $results[] = $soloRecords[$hash];
                } else {
                    $relations = $joinTables[$table];
                    $parentTableName = $relations->getKnownTableName();
                    $parentHash = $row[$parentTableName];
                    if (!isset($tableHasChildren[$parentHash])) { // parent table was not used
                        $tableHasChildren[$parentHash] = [];
                    }
                    if (!in_array($hash, $tableHasChildren[$parentHash])) { // connect it only once
                        $this->addChild(
                            $relations->getJoinUnderAlias(),
                            $soloRecords[$parentHash],
                            $soloRecords[$hash]
                        );
                        $tableHasChildren[$parentHash][] = $hash;
                    }
                }
            }
        }

        return $results;
    }

    protected function addChild(string $joinAlias, &$parent, &$current)
    {
        $parent->{$joinAlias} = empty($parent->{$joinAlias})
            ? [$current]
            : $parent->{$joinAlias} + [$current]
        ;
    }

    private function setAsFromDatabase(&$class): void
    {
        if (is_object($class)) {
            if ($class instanceof Connector\Database) {
                $this->fromDatabase = true;
                return;
            }
            // another for mapper - probably...
        }
        $this->fromDatabase = false;
    }

    /**
     * @param Join[] $joins
     * @return Join[]
     */
    protected function fillJoins(array &$joins): array
    {
        $result = [];
        $result[$this->record->getMapper()->getAlias()] = null;
        foreach ($joins as $join) {
            $key = empty($join->getTableAlias()) ? $join->getJoinUnderAlias() : $join->getTableAlias() ;
            if (isset($result[$key])) {
                continue;
            }
            $result[$key] = $join;
        }
        return $result;
    }

    protected function splitByTables(&$row): array
    {
        $byTables = [];
        foreach ($row as $column => &$data) {
            $delimiterPoint = strpos($column, '.'); // look for delimiter, not everytime is present
            $delimiterOur = strpos($column, $this->columnDelimiter); // our delimiter, because some databases returns only columns
            if ((false === $delimiterPoint) && (false === $delimiterOur)) {
                $table = $this->record->getMapper()->getAlias();
            } elseif (false === $delimiterPoint) { // database returns our delimiter
                $table = substr($column, 0, $delimiterOur);
                $column = substr($column, $delimiterOur + strlen($this->columnDelimiter));
            } else {
                $table = substr($column, 0, $delimiterPoint);
                $column = substr($column, $delimiterPoint + 1);
            }
            $byTables[$table][$column] = $data;
        }
        return $byTables;
    }

    /**
     * @param ARecord $record
     * @param iterable $columns
     * @return ARecord
     * @throws MapperException
     */
    protected function fillTableRecord(ARecord $record, iterable $columns): ARecord
    {
        $flippedRelations = array_flip($record->getMapper()->getRelations());
        foreach ($columns as $index => &$data) {
            $entry = $record->getEntry($flippedRelations[$index]);
            $entry->setData($this->typedFillSelection($entry, $data), $this->fromDatabase);
        }
        return $record;
    }
}

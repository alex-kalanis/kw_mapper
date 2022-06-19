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
 *
 * Start with getColumns() to decide which columns will be get from DB
 * After the data will be obtained pass them through fillResults() to fill records itself
 */
class Filler
{
    use TFill;

    /** @var string */
    protected $hashDelimiter = "--::\e::--";
    /** @var string */
    protected $columnDelimiter = '____';
    /** @var ARecord|null */
    protected $basicRecord = null;
    /** @var RecordsInJoin[] */
    protected $recordsInJoin = [];
    /** @var bool */
    private $fromDatabase = false;

    public function __construct(ARecord $basicRecord)
    {
        $this->basicRecord = $basicRecord;
    }

    /**
     * @param RecordsInJoin[] $recordsInJoin
     */
    public function initTreeSolver(array &$recordsInJoin): void
    {
        $this->recordsInJoin = $recordsInJoin;
    }

    /**
     * @param Join[] $joins
     * @return array<string|int, array<string|int|float|null>>
     */
    public function getColumns(array $joins): array
    {
        $used = [];
        $columns = [];
        $join = $this->orderJoinsColumns($joins);
        foreach ($this->recordsInJoin as &$record) {
            $alias = $record->getStoreKey();
            if (in_array($alias, $used)) {
                // @codeCoverageIgnoreStart
                // if they came here more than once
                // usually happens when the circular dependency came - the child has child which is the same record
                continue;
            }
            // @codeCoverageIgnoreEnd
            foreach ($record->getRecord()->getMapper()->getRelations() as $relation) {
                $joinAlias = empty($join[$alias]) ? $alias : $join[$alias];
                $columns[] = [$joinAlias, $relation, implode($this->columnDelimiter, [$joinAlias, $relation])];
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
     * @param iterable<string|int, array<string|int, string|int|float>> $dataSourceRows
     * @param mixed $parent
     * @throws MapperException
     * @return ARecord[]
     */
    public function fillResults(iterable $dataSourceRows, $parent = null): array
    {
        $this->setAsFromDatabase($parent);
        /** @var array<string, array<string, ARecord>> */
        $aliasedRecords = [];
        /** @var array<string|int, array<string, string>> */
        $hashedRows = [];
        // parse input data into something processable
        // got records with elementary data and hashes of them
        foreach ($dataSourceRows as $lineNo => &$row) {
            // get each table from resulted row
            $splitRow = $this->splitByTables($row);
//print_r(['row', $splitRow]);

            // now put each table into record
            foreach ($splitRow as $alias => &$columns) {
                $hashedRecord = $this->hashRow($columns);
                // store info which row is it
                if (!isset($hashedRows[$lineNo])) {
                    $hashedRows[$lineNo] = [];
                }
                $hashedRows[$lineNo][$alias] = $hashedRecord;
                if (is_null($hashedRecord)) {
                    // skip for empty content
                    continue;
                }
                // store that record
                if (!isset($aliasedRecords[$alias])) {
                    $aliasedRecords[$alias] = [];
                }
                if (isset($aliasedRecords[$alias][$hashedRecord])) {
                    // skip for existing
                    continue;
                }
                $aliasedRecords[$alias][$hashedRecord] = $this->fillRecordFromAlias($alias, $columns);
            }
        }

//print_r(['hashes rec', $aliasedRecords]); // records of each table in each row keyed to their hash --> $aliasedRecords[table_name][hash] = Record
//print_r(['hashes row', $hashedRows]); // line contains --> $hashedRows[line_number][table_name] = hash

        // tell which alias is parent of another - only by hashes
        $parentsAliases = $this->getParentsAliases();
        /** @var array<string, array<string, array<string, string[]>>> $children */
        $children = [];
        foreach ($hashedRows as &$hashedRow) {
            foreach ($parentsAliases as $currentAlias => &$parentsAlias) {
                if (empty($hashedRow[$parentsAlias])) { // top parent
                    continue;
                }
                $currentHash = $hashedRow[$currentAlias];
                $parentHash = $hashedRow[$parentsAlias];
                // from parent aliases which will be called to fill add child aliases with their content
                if (!isset($children[$parentsAlias])) {
                    $children[$parentsAlias] = [];
                }
                if (!isset($children[$parentsAlias][$parentHash])) {
                    $children[$parentsAlias][$parentHash] = [];
                }
                if (!isset($children[$parentsAlias][$parentHash][$currentAlias])) {
                    $children[$parentsAlias][$parentHash][$currentAlias] = [];
                }
                // can be more than one child for parent
                if (!empty($currentHash)) {
                    $children[$parentsAlias][$parentHash][$currentAlias][] = $currentHash;
                }
            }
        }

//print_r(['hashes children', $children]);

        // now put records together as they're defined by their hashes
        foreach ($children as $parentAlias => &$hashes) {
            foreach ($hashes as $parentHash => &$childrenHashes) {
                /** @var ARecord $record */
                $record = $aliasedRecords[$parentAlias][$parentHash];

                foreach ($childrenHashes as $childAlias => $childrenHashArr) {
                    $records = [];
                    $aliasParams = $this->getRecordForAlias($childAlias);
                    foreach ($childrenHashArr as $hash) {
                        $records[] = $aliasedRecords[$childAlias][$hash];
                    }
                    $record->getEntry($aliasParams->getKnownAs())->setData($records, $this->fromDatabase);
                }
            }
        }

        $results = array_values($aliasedRecords[$this->getRecordForRoot()->getStoreKey()]);
//print_r(['count res', count($results) ]);

        return $results;
    }

    /**
     * @param array<string|int, string|int|float|null> $columns
     * @return string|null
     */
    protected function hashRow(array &$columns): ?string
    {
        $cols = implode($this->hashDelimiter, $columns);
        if (empty(str_replace($this->hashDelimiter, '', $cols))) {
            return null;
        }
        return md5($cols);
    }

    /**
     * @param string $alias
     * @param array<string|int, string|int|float|null> $columns
     * @throws MapperException
     * @return ARecord
     */
    protected function fillRecordFromAlias(string $alias, array &$columns): ARecord
    {
        $original = $this->getRecordForAlias($alias)->getRecord();
        $record = clone $original;
        $properties = array_flip($record->getMapper()->getRelations());
        foreach ($columns as $column => $value) {
            if (isset($properties[$column])) {
                $property = strval($properties[$column]);
                if ($record->offsetExists($property) && ($record->offsetGet($property)) !== $value) {
                    $record->getEntry($property)->setData($value, $this->fromDatabase);
                }
            }
        }
        return $record;
    }

    /**
     * @param string $alias
     * @throws MapperException
     * @return RecordsInJoin
     */
    protected function getRecordForAlias(string $alias): RecordsInJoin
    {
        foreach ($this->recordsInJoin as $recordInJoin) {
            if ($recordInJoin->getStoreKey() == $alias) {
                return $recordInJoin;
            }
        }
        throw new MapperException(sprintf('No record for alias *%s* found.', $alias));
    }

    /**
     * @throws MapperException
     * @return RecordsInJoin
     */
    protected function getRecordForRoot(): RecordsInJoin
    {
        foreach ($this->recordsInJoin as $recordInJoin) {
            if (is_null($recordInJoin->getParentAlias())) {
                return $recordInJoin;
            }
        }
        throw new MapperException(sprintf('No root record found.'));
    }

    /**
     * @return array<string, string|null>
     */
    protected function getParentsAliases(): array
    {
        $result = [];
        foreach ($this->recordsInJoin as &$recordInJoin) {
            $result[$recordInJoin->getStoreKey()] = $recordInJoin->getParentAlias();
        }
        return $result;
    }

    /**
     * @param mixed $class
     */
    private function setAsFromDatabase($class): void
    {
        if ($class && is_object($class)) {
            if ($class instanceof Connector\Database) {
                $this->fromDatabase = true;
                return;
            }
            // another for other possible connectors - probably...
        }
        $this->fromDatabase = false;
    }

    /**
     * @param array<string|int, string|int|float> $row
     * @return array<string, array<string|int, string|int|float>>
     */
    protected function splitByTables(&$row): array
    {
        $byTables = [];
        foreach ($row as $column => &$data) {
            $column = strval($column);
            $delimiterPoint = strpos($column, '.'); // look for delimiter, not every time is present
            $delimiterOur = strpos($column, $this->columnDelimiter); // our delimiter, because some databases returns only columns
            if ((false === $delimiterPoint) && (false === $delimiterOur)) {
                $table = $this->basicRecord->getMapper()->getAlias();
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
}

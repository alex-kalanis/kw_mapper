<?php

namespace kalanis\kw_mapper\Search\Connector;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Storage;


/**
 * Class Records
 * @package kalanis\kw_mapper\Search
 * Connect records behaving as datasource
 * Behave only as advanced filtering
 */
class Records extends AConnector
{
    /** @var ARecord[] */
    protected $initialRecords = [];
    /** @var null|Storage\Shared\QueryBuilder\Condition */
    protected $condition = null;
    /** @var null|Storage\Shared\QueryBuilder\Order */
    protected $sortingOrder = null;

    /**
     * @param ARecord $record
     * @throws MapperException
     */
    public function __construct(ARecord $record)
    {
        $this->basicRecord = $record;
        $this->initRecordLookup($record); // correct column
        $this->queryBuilder = $this->initQueryBuilder();
        $this->queryBuilder->setBaseTable($record->getMapper()->getAlias());
    }

    protected function initQueryBuilder(): Storage\Shared\QueryBuilder
    {
        return new Storage\Shared\QueryBuilder();
    }

    /**
     * @param ARecord[] $initialRecords
     */
    public function setInitialRecords(array $initialRecords): void
    {
        $this->initialRecords = array_filter($initialRecords, [$this, 'filterInitial']);
    }

    /**
     * @param mixed $record
     * @return bool
     */
    public function filterInitial($record): bool
    {
        $class = get_class($this->basicRecord);
        return is_object($record) && ($record instanceof $class);
    }

    /**
     * @param string $table
     * @return string
     * The table here represents a unknown entity, in which it's saved - can even be no storage and the whole can be
     * initialized on-the-fly. So return no table. Also good for files where the storage points to the whole file path.
     * In fact in some SQL engines it's also real file on volume.
     */
    protected function correctTable(string $table): string
    {
        return '';
    }

    /**
     * @param string $table
     * @param string $column
     * @throws MapperException
     * @return string
     */
    protected function correctColumn(string $table, string $column)
    {
        $relations = $this->basicRecord->getMapper()->getRelations();
        if (!isset($relations[$column])) {
            throw new MapperException(sprintf('Unknown relation key *%s* in mapper for table *%s*', $column, $this->basicRecord->getMapper()->getAlias()));
        }
        return $column;
    }

    public function child(string $childAlias, string $joinType = IQueryBuilder::JOIN_LEFT, string $parentAlias = '', string $customAlias = ''): parent
    {
        throw new MapperException('Cannot make relations over already loaded records!');
    }

    public function childNotExist(string $childAlias, string $table, string $column, string $parentAlias = ''): parent
    {
        throw new MapperException('Cannot make relations over already loaded records!');
    }

    public function getCount(): int
    {
        return count($this->getResults(false));
    }

    /**
     * @param bool $limited
     * @throws MapperException
     * @return ARecord[]
     */
    public function getResults(bool $limited = true): array
    {
        $results = $this->sortResults( // sorting
                $this->filterResults( // filters after grouping
                $this->groupResults( // grouping
                    $this->filterResults( // basic filters
                        $this->getInitialRecords(), // all records
                        $this->queryBuilder->getConditions()
                    ),
                    $this->queryBuilder->getGrouping()
                ),
                $this->queryBuilder->getHavingCondition()
            ),
            $this->queryBuilder->getOrdering()
        );

        // paging
        return $limited
            ? array_slice($results, intval($this->queryBuilder->getOffset()), $this->queryBuilder->getLimit())
            : $results ;
    }

    /**
     * @return ARecord[]
     */
    protected function getInitialRecords(): array
    {
        return $this->initialRecords;
    }

    /**
     * @param ARecord[] $records
     * @param Storage\Shared\QueryBuilder\Condition[] $conditions
     * @return ARecord[]
     */
    protected function filterResults(array $records, array $conditions): array
    {
        foreach ($conditions as $condition) {
            $this->condition = $condition;
            $records = array_filter($records, [$this, 'filterCondition']);
        }
        $this->condition = null;
        return $records;
    }

    /**
     * @param ARecord[] $records
     * @param Storage\Shared\QueryBuilder\Group[] $grouping
     * @throws MapperException
     * @return ARecord[]
     * Each one in group must have the same value; one difference = another group
     */
    protected function groupResults(array $records, array $grouping): array
    {
        // no groups - no process
        if (empty($grouping)) {
            return $records;
        }
        // get indexes of groups
        $indexes = [];
        foreach ($grouping as $group) {
            $indexes[] = $group->getColumnName();
        }
        $keys = [];
        // over records...
        foreach ($records as $record) {
            $key = [];
            // get value of each element wanted for grouping
            foreach ($indexes as $index) {
                $key[] = strval($record->offsetGet($index));
            }
            // create key which represents that element from the angle of view of groups
            $expected = implode('__', $key);
            // and check if already exists - add if not
            if (!isset($keys[$expected])) {
                $keys[$expected] = $record;
            }
        }
        // here stays only the first one
        return $keys;
    }

    /**
     * @param ARecord[] $records
     * @param Storage\Shared\QueryBuilder\Order[] $ordering
     * @return ARecord[]
     */
    protected function sortResults(array $records, array $ordering): array
    {
        foreach ($ordering as $order) {
            $this->sortingOrder = $order;
            usort($records, [$this, 'sortOrder']);
        }
        $this->sortingOrder = null;
        return $records;
    }

    /**
     * @param ARecord $result
     * @throws MapperException
     * @return bool
     */
    public function filterCondition(ARecord $result): bool
    {
        if (empty($this->condition)) {
            throw new MapperException('You must set conditions first!');
        }
        $columnKey = $this->condition->getColumnKey();
        return is_array($columnKey)
            ? $this->filterFromManyValues($this->condition->getOperation(), $result->offsetGet($this->condition->getColumnName()), $this->queryBuilder->getParams(), $columnKey)
            : $this->checkCondition($this->condition->getOperation(), $result->offsetGet($this->condition->getColumnName()), $this->queryBuilder->getParams()[$columnKey] )
        ;
    }

    /**
     * @param string $operation
     * @param mixed $value
     * @param array<string, int|string|float|null> $params
     * @param string[] $columnKeys
     * @throws MapperException
     * @return bool
     */
    protected function filterFromManyValues(string $operation, $value, array $params, array $columnKeys): bool
    {
        $values = [];
        foreach ($columnKeys as $columnKey) {
            $values[$columnKey] = $params[$columnKey];
        }
        return $this->checkCondition($operation, $value, $values);
    }

    /**
     * @param string $operation
     * @param mixed $value
     * @param mixed $expected
     * @throws MapperException
     * @return bool
     */
    protected function checkCondition(string $operation, $value, $expected): bool
    {
        switch ($operation) {
            case IQueryBuilder::OPERATION_NULL:
                return is_null($value);
            case IQueryBuilder::OPERATION_NNULL:
                return !is_null($value);
            case IQueryBuilder::OPERATION_EQ:
                return $expected == $value;
            case IQueryBuilder::OPERATION_NEQ:
                return $expected != $value;
            case IQueryBuilder::OPERATION_GT:
                return $expected < $value;
            case IQueryBuilder::OPERATION_GTE:
                return $expected <= $value;
            case IQueryBuilder::OPERATION_LT:
                return $expected > $value;
            case IQueryBuilder::OPERATION_LTE:
                return $expected >= $value;
            case IQueryBuilder::OPERATION_LIKE:
                return false !== strpos($value, $expected);
            case IQueryBuilder::OPERATION_NLIKE:
                return false === strpos($value, $expected);
            case IQueryBuilder::OPERATION_REXP:
                return 1 === preg_match($expected, $value);
            case IQueryBuilder::OPERATION_IN:
                return in_array($value, (array) $expected);
            case IQueryBuilder::OPERATION_NIN:
                return !in_array($value, (array) $expected);
            default:
                throw new MapperException(sprintf('Unknown operation *%s* for comparation.', $operation));
        }
    }

    /**
     * @param ARecord $resultA
     * @param ARecord $resultB
     * @throws MapperException
     * @return int
     */
    public function sortOrder(ARecord $resultA, ARecord $resultB): int
    {
        if (empty($this->sortingOrder)) {
            throw new MapperException('You must set how it will be sorted!');
        }
        $sortingDirection = empty($this->sortingOrder->getDirection()) ? IQueryBuilder::ORDER_ASC : $this->sortingOrder->getDirection();
        $a = $resultA->offsetGet($this->sortingOrder->getColumnName());
        $b = $resultB->offsetGet($this->sortingOrder->getColumnName());

        return (IQueryBuilder::ORDER_ASC == $sortingDirection) ? $a <=> $b : $b <=> $a ;
    }
}

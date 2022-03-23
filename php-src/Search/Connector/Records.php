<?php

namespace kalanis\kw_mapper\Search\Connector;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Storage;


/**
 * Class FileTable
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

    public function __construct(ARecord $record)
    {
        $this->basicRecord = $record;
        $this->records[$record->getMapper()->getAlias()] = $record; // correct column
        $this->queryBuilder = new Storage\Shared\QueryBuilder();
        $this->queryBuilder->setBaseTable($record->getMapper()->getAlias());
    }

    /**
     * @param ARecord[] $initialRecords
     */
    public function setInitialRecords(array $initialRecords): void
    {
        $this->initialRecords = array_filter($initialRecords, [$this, 'filterInitial']);
    }

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
     * @return string
     * @throws MapperException
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

    public function childTree(string $childAlias): array
    {
        throw new MapperException('Cannot access relations over already loaded records!');
    }

    public function getCount(): int
    {
        return count($this->getResults(false));
    }

    /**
     * @param bool $limited
     * @return ARecord[]
     */
    public function getResults(bool $limited = true): array
    {
        $results = $this->getInitialRecords();

        foreach ($this->queryBuilder->getConditions() as $condition) {
            $this->condition = $condition;
            $results = array_filter($results, [$this, 'filterCondition']);
        }
        $this->condition = null;

        foreach ($this->queryBuilder->getOrdering() as $order) {
            $this->sortingOrder = $order;
            usort($results, [$this, 'sortOrder']);
        }
        $this->sortingOrder = null;

        return $limited
            ? array_slice($results, intval($this->queryBuilder->getOffset()), $this->queryBuilder->getLimit())
            : $results ;
    }

    protected function getInitialRecords(): array
    {
        return $this->initialRecords;
    }

    /**
     * @param ARecord $result
     * @return bool
     * @throws MapperException
     */
    public function filterCondition(ARecord $result)
    {
        return is_array($this->condition->getColumnKey())
            ? $this->filterManyValues($result, $this->condition->getColumnKey())
            : $this->filterOneCondition($result, $this->queryBuilder->getParams()[$this->condition->getColumnKey()])
        ;
    }

    /**
     * @param ARecord $result
     * @param string[] $columnKeys
     * @return bool
     * @throws MapperException
     */
    protected function filterManyValues(ARecord $result, array $columnKeys): bool
    {
        $values = [];
        foreach ($columnKeys as $columnKey) {
            $values[$columnKey] = $this->queryBuilder->getParams()[$columnKey];
        }
        return $this->filterOneCondition($result, $values);
    }

    /**
     * @param ARecord $result
     * @param string|string[] $expectedValue
     * @return bool
     * @throws MapperException
     */
    protected function filterOneCondition(ARecord $result, $expectedValue): bool
    {
        return $this->checkCondition(
            $this->condition->getOperation(),
            $result->offsetGet($this->condition->getColumnName()),
            $expectedValue
        );
    }

    /**
     * @param string $operation
     * @param mixed $value
     * @param mixed $expected
     * @return bool
     * @throws MapperException
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
                return in_array($value, (array)$expected);
            case IQueryBuilder::OPERATION_NIN:
                return !in_array($value, (array)$expected);
            default:
                throw new MapperException(sprintf('Unknown operation *%s* for comparation.', $operation));
        }
    }

    /**
     * @param ARecord $resultA
     * @param ARecord $resultB
     * @return int
     * @throws MapperException
     */
    public function sortOrder(ARecord $resultA, ARecord $resultB): int
    {
        $sortingDirection = empty($this->sortingOrder->getDirection()) ? IQueryBuilder::ORDER_ASC : $this->sortingOrder->getDirection();
        $a = $resultA->offsetGet($this->sortingOrder->getColumnName());
        $b = $resultB->offsetGet($this->sortingOrder->getColumnName());

        return (IQueryBuilder::ORDER_ASC == $sortingDirection) ? $a <=> $b : $b <=> $a ;
    }
}

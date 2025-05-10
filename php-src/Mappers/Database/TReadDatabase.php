<?php

namespace kalanis\kw_mapper\Mappers\Database;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Shared\TEntityChanged;
use kalanis\kw_mapper\Mappers\Shared\TFilterNulls;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\TFill;
use kalanis\kw_mapper\Storage\Database;


/**
 * Trait TReadDatabase
 * @package kalanis\kw_mapper\Mappers\Database
 * Separated read DB entry without need to reload mapper
 *
 * Contains only operations for reading from DB
 */
trait TReadDatabase
{
    use TEntityChanged;
    use TFill;
    use TFilterNulls;

    protected string $readSource = '';
    protected Database\ASQL $readDatabase;
    protected Database\Dialects\ADialect $readDialect;
    protected Database\QueryBuilder $readQueryBuilder;

    /**
     * @throws MapperException
     */
    protected function initTReadDatabase(): void
    {
        $this->readDatabase = $this->getReadDatabase();
        $this->readDialect = $this->getReadDialect($this->readDatabase);
        $this->readQueryBuilder = $this->getReadQueryBuilder($this->readDialect);
    }

    protected function setReadSource(string $readSource): void
    {
        $this->readSource = $readSource;
    }

    protected function getReadSource(): string
    {
        return $this->readSource;
    }

    /**
     * @throws MapperException
     * @return Database\ADatabase
     */
    protected function getReadDatabase(): Database\ADatabase
    {
        return Database\DatabaseSingleton::getInstance()->getDatabase(
            Database\ConfigStorage::getInstance()->getConfig($this->getReadSource())
        );
    }

    /**
     * @param Database\ADatabase $database
     * @throws MapperException
     * @return Database\Dialects\ADialect
     */
    protected function getReadDialect(Database\ADatabase $database): Database\Dialects\ADialect
    {
        return Database\Dialects\Factory::getInstance()->getDialectClass($database->languageDialect());
    }

    protected function getReadQueryBuilder(Database\Dialects\ADialect $dialect): Database\QueryBuilder
    {
        return new Database\QueryBuilder($dialect);
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function loadRecord(ARecord $record): bool
    {
        if ($this->loadRecordByPk($record)) {
            return true;
        }

        $this->readQueryBuilder->clear();
        $this->readQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        // conditions - must be equal
        foreach ($record as $key => $item) {
            if (isset($relations[$key]) && $this->ifEntryChanged($record->getEntry($key))) {
                $this->readQueryBuilder->addCondition(
                    $record->getMapper()->getAlias(),
                    $relations[$key],
                    IQueryBuilder::OPERATION_EQ,
                    $item
                );
            }
        }

        // relations - what to get
        foreach ($relations as $localAlias => $remoteColumn) {
            $this->readQueryBuilder->addColumn($record->getMapper()->getAlias(), $remoteColumn, $localAlias);
        }
        $this->readQueryBuilder->setLimits(0,1);

        // query itself
        $lines = $this->readDatabase->query(
            strval($this->readDialect->select($this->readQueryBuilder)),
            array_filter($this->readQueryBuilder->getParams(), [$this, 'filterNullValues'])
        );
        if (empty($lines)) {
            return false;
        }

        // fill entries in record
        $line = reset($lines);
        foreach ($line as $index => $item) {
            $entry = $record->getEntry($index);
            $entry->setData($this->typedFillSelection($entry, $item), true);
        }
        return true;
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function loadRecordByPk(ARecord $record): bool
    {
        if (empty($record->getMapper()->getPrimaryKeys())) {
            return false;
        }

        $this->readQueryBuilder->clear();
        $this->readQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        // conditions - everything must be equal
        foreach ($record->getMapper()->getPrimaryKeys() as $key) {
            try {
                if (isset($relations[$key]) && $this->ifEntryChanged($record->getEntry($key))) {
                    $this->readQueryBuilder->addCondition(
                        $record->getMapper()->getAlias(),
                        $relations[$key],
                        IQueryBuilder::OPERATION_EQ,
                        $record->offsetGet($key)
                    );
                }
            } catch (MapperException $ex) {
                return false;
            }
        }

        if (empty($this->readQueryBuilder->getConditions())) { // no conditions, nothing in PKs - back to normal system
            return false;
        }

        // relations - what to get
        foreach ($relations as $localAlias => $remoteColumn) {
            $this->readQueryBuilder->addColumn($record->getMapper()->getAlias(), $remoteColumn, $localAlias);
        }

        // query itself
        $this->readQueryBuilder->setLimits(0,1);
        $lines = $this->readDatabase->query(
            strval($this->readDialect->select($this->readQueryBuilder)),
            array_filter($this->readQueryBuilder->getParams(), [$this, 'filterNullValues'])
        );
        if (empty($lines)) {
            return false;
        }

        // fill entries in record
        $line = reset($lines);
        foreach ($line as $index => $item) {
            $entry = $record->getEntry($index);
            $entry->setData($this->typedFillSelection($entry, $item), true);
        }
        return true;
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return int
     */
    public function countRecord(ARecord $record): int
    {
        $readQueryBuilder = clone $this->readQueryBuilder;
        $readQueryBuilder->clear();
        $readQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        foreach ($record as $key => $item) {
            if (isset($relations[$key]) && $this->ifEntryChanged($record->getEntry($key))) {
                $readQueryBuilder->addCondition(
                    $record->getMapper()->getAlias(),
                    $relations[$key],
                    IQueryBuilder::OPERATION_EQ,
                    $item
                );
            }
        }

        if (empty($record->getMapper()->getPrimaryKeys())) {
            $relation = reset($relations);
            if (false !== $relation) {
                $readQueryBuilder->addColumn(
                    $record->getMapper()->getAlias(),
                    $relation,
                    'count',
                    IQueryBuilder::AGGREGATE_COUNT
                );
            }
        } else {
//            foreach ($record->getMapper()->getPrimaryKeys() as $primaryKey) {
//                $readQueryBuilder->addColumn(
//                    $record->getMapper()->getAlias(),
//                    $primaryKey,
//                    '',
//                    IQueryBuilder::AGGREGATE_COUNT
//                );
//            }
            $pks = $record->getMapper()->getPrimaryKeys();
            $key = reset($pks);
            $readQueryBuilder->addColumn(
                $record->getMapper()->getAlias(),
                $relations[$key],
                'count',
                IQueryBuilder::AGGREGATE_COUNT
            );
        }

        $lines = $this->readDatabase->query(
            strval($this->readDialect->select($readQueryBuilder)),
            array_filter($readQueryBuilder->getParams(), [$this, 'filterNullValues'])
        );
        if (empty($lines) || !is_iterable($lines)) {
            // @codeCoverageIgnoreStart
            return 0;
        }
        // @codeCoverageIgnoreEnd
        $line = reset($lines);
        return intval(reset($line));
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return ARecord[]
     */
    public function loadMultiple(ARecord $record): array
    {
        $readQueryBuilder = clone $this->readQueryBuilder;
        $readQueryBuilder->clear();
        $readQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        foreach ($record as $key => $item) {
            if (isset($relations[$key]) && $this->ifEntryChanged($record->getEntry($key))) {
                $readQueryBuilder->addCondition(
                    $record->getMapper()->getAlias(),
                    $relations[$key],
                    IQueryBuilder::OPERATION_EQ,
                    $item
                );
            }
        }

        // relations - what to get
        foreach ($relations as $localAlias => $remoteColumn) {
            $readQueryBuilder->addColumn($record->getMapper()->getAlias(), $remoteColumn, $localAlias);
        }
        if (empty($readQueryBuilder->getOrdering())) {
            foreach ($record->getMapper()->getPrimaryKeys() as $primaryKey) {
                $readQueryBuilder->addOrderBy($readQueryBuilder->getBaseTable(), $relations[$primaryKey], IQueryBuilder::ORDER_ASC);
            }
        }

        // query itself
        $lines = $this->readDatabase->query(
            strval($this->readDialect->select($readQueryBuilder)),
            array_filter($readQueryBuilder->getParams(), [$this, 'filterNullValues'])
        );
        if (empty($lines)) {
            return [];
        }

        $result = [];
        foreach ($lines as $line) {
            $rec = clone $record;
            foreach ($line as $index => $item) {
                $entry = $rec->getEntry($index);
                $entry->setData($this->typedFillSelection($entry, $item), true);
            }
            $result[] = $rec;
        }
        return $result;
    }
}

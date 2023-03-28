<?php

namespace kalanis\kw_mapper\Mappers\Database;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\AMapper;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\TFill;
use kalanis\kw_mapper\Storage;


/**
 * Class AReadWriteDatabase
 * @package kalanis\kw_mapper\Mappers\Database
 * Separated Read and write DB entry without need to reload mapper
 * The most parts are similar to usual read/write one, just with separation of read-write operations
 */
abstract class AReadWriteDatabase extends AMapper
{
    use TFill;
    use TTable;

    /** @var string */
    protected $readSource = '';
    /** @var string */
    protected $writeSource = '';
    /** @var Storage\Database\ASQL */
    protected $readDatabase = null;
    /** @var Storage\Database\ASQL */
    protected $writeDatabase = null;
    /** @var Storage\Database\Dialects\ADialect */
    protected $readDialect = null;
    /** @var Storage\Database\Dialects\ADialect */
    protected $writeDialect = null;
    /** @var Storage\Database\QueryBuilder */
    protected $readQueryBuilder = null;
    /** @var Storage\Database\QueryBuilder */
    protected $writeQueryBuilder = null;

    /**
     * @throws MapperException
     */
    public function __construct()
    {
        parent::__construct();

        // read part
        $readConfig = Storage\Database\ConfigStorage::getInstance()->getConfig($this->getReadSource());
        $this->readDatabase = Storage\Database\DatabaseSingleton::getInstance()->getDatabase($readConfig);
        $this->readDialect = Storage\Database\Dialects\Factory::getInstance()->getDialectClass($this->readDatabase->languageDialect());
        $this->readQueryBuilder = new Storage\Database\QueryBuilder($this->readDialect);

        // write part
        $writeConfig = Storage\Database\ConfigStorage::getInstance()->getConfig($this->getWriteSource());
        $this->writeDatabase = Storage\Database\DatabaseSingleton::getInstance()->getDatabase($writeConfig);
        $this->writeDialect = Storage\Database\Dialects\Factory::getInstance()->getDialectClass($this->writeDatabase->languageDialect());
        $this->writeQueryBuilder = new Storage\Database\QueryBuilder($this->writeDialect);
    }

    protected function setReadSource(string $readSource): void
    {
        $this->readSource = $readSource;
    }

    protected function getReadSource(): string
    {
        return $this->readSource;
    }

    protected function setWriteSource(string $writeSource): void
    {
        $this->writeSource = $writeSource;
    }

    protected function getWriteSource(): string
    {
        return $this->writeSource;
    }

    public function getAlias(): string
    {
        return $this->getTable();
    }

    protected function insertRecord(ARecord $record): bool
    {
        $this->writeQueryBuilder->clear();
        $this->writeQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        foreach ($record as $key => $item) {
            if (isset($relations[$key]) && (false !== $item)) {
                $this->writeQueryBuilder->addProperty($record->getMapper()->getAlias(), $relations[$key], $item);
            }
        }
        if (empty($this->writeQueryBuilder->getProperties())) {
            return false;
        }

        return $this->writeDatabase->exec(strval($this->writeDialect->insert($this->writeQueryBuilder)), $this->writeQueryBuilder->getParams());
    }

    protected function updateRecord(ARecord $record): bool
    {
        if ($this->updateRecordByPk($record)) {
            return true;
        }
        $this->writeQueryBuilder->clear();
        $this->writeQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        foreach ($record as $key => $item) {
            if (isset($relations[$key]) && (false !== $item)) {
                if ($record->getEntry($key)->isFromStorage()) {
                    $this->writeQueryBuilder->addCondition($record->getMapper()->getAlias(), $relations[$key], IQueryBuilder::OPERATION_EQ, $item);
                } else {
                    $this->writeQueryBuilder->addProperty($record->getMapper()->getAlias(), $relations[$key], $item);
                }
            }
        }
        if (empty($this->writeQueryBuilder->getConditions())) { /// this one is questionable - I really want to update everything?
            return false;
        }
        if (empty($this->writeQueryBuilder->getProperties())) {
            return false;
        }

        return $this->writeDatabase->exec(strval($this->writeDialect->update($this->writeQueryBuilder)), $this->writeQueryBuilder->getParams());
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function updateRecordByPk(ARecord $record): bool
    {
        if (empty($record->getMapper()->getPrimaryKeys())) {
            return false;
        }

        $this->writeQueryBuilder->clear();
        $this->writeQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        foreach ($record->getMapper()->getPrimaryKeys() as $key) {
            try {
                if (isset($relations[$key])) {
                    $entry = $record->getEntry($key);
                    if ($entry->isFromStorage() && (false !== $entry->getData())) {
                        $this->writeQueryBuilder->addCondition($record->getMapper()->getAlias(), $relations[$key], IQueryBuilder::OPERATION_EQ, $entry->getData());
                    }
                }
            } catch (MapperException $ex) {
                return false;
            }
        }

        if (empty($this->writeQueryBuilder->getConditions())) { // no conditions, nothing in PKs - back to normal system
            return false;
        }

        foreach ($record as $key => $item) {
            if (isset($relations[$key])) {
                $entry = $record->getEntry($key);
                if (!in_array($key, $record->getMapper()->getPrimaryKeys()) && !$entry->isFromStorage() && (false !== $item)) {
                    $this->writeQueryBuilder->addProperty($record->getMapper()->getAlias(), $relations[$key], $item);
                }
            }
        }
        if (empty($this->writeQueryBuilder->getProperties())) {
            return false;
        }

        return $this->writeDatabase->exec(strval($this->writeDialect->update($this->writeQueryBuilder)), $this->writeQueryBuilder->getParams());
    }

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
            if (isset($relations[$key]) && (false !== $item)) {
                $this->readQueryBuilder->addCondition($record->getMapper()->getAlias(), $relations[$key], IQueryBuilder::OPERATION_EQ, $item);
            }
        }

        // relations - what to get
        foreach ($relations as $localAlias => $remoteColumn) {
            $this->readQueryBuilder->addColumn($record->getMapper()->getAlias(), $remoteColumn, $localAlias);
        }
        $this->readQueryBuilder->setLimits(0,1);

        // query itself
        $lines = $this->readDatabase->query(strval($this->readDialect->select($this->readQueryBuilder)), $this->readQueryBuilder->getParams());
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
                if (isset($relations[$key])) {
                    $item = $record->offsetGet($key);
                    if (false !== $item) {
                        $this->readQueryBuilder->addCondition($record->getMapper()->getAlias(), $relations[$key], IQueryBuilder::OPERATION_EQ, $item);
                    }
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
        $lines = $this->readDatabase->query(strval($this->readDialect->select($this->readQueryBuilder)), $this->readQueryBuilder->getParams());
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

    public function countRecord(ARecord $record): int
    {
        $this->readQueryBuilder->clear();
        $this->readQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        foreach ($record as $key => $item) {
            if (isset($relations[$key]) && (false !== $item)) {
                $this->readQueryBuilder->addCondition($record->getMapper()->getAlias(), $relations[$key], IQueryBuilder::OPERATION_EQ, $item);
            }
        }

        if (empty($record->getMapper()->getPrimaryKeys())) {
            $relation = reset($relations);
            if (false !== $relation) {
                $this->readQueryBuilder->addColumn($record->getMapper()->getAlias(), $relation, 'count', IQueryBuilder::AGGREGATE_COUNT);
            }
        } else {
//            foreach ($record->getMapper()->getPrimaryKeys() as $primaryKey) {
//                $this->readQueryBuilder->addColumn($record->getMapper()->getAlias(), $primaryKey, '', IQueryBuilder::AGGREGATE_COUNT);
//            }
            $pks = $record->getMapper()->getPrimaryKeys();
            $key = reset($pks);
            $this->readQueryBuilder->addColumn($record->getMapper()->getAlias(), $relations[$key], 'count', IQueryBuilder::AGGREGATE_COUNT);
        }

        $lines = $this->readDatabase->query(strval($this->readDialect->select($this->readQueryBuilder)), $this->readQueryBuilder->getParams());
        if (empty($lines) || !is_iterable($lines)) {
            // @codeCoverageIgnoreStart
            return 0;
        }
        // @codeCoverageIgnoreEnd
        $line = reset($lines);
        return intval(reset($line));
    }

    protected function deleteRecord(ARecord $record): bool
    {
        if ($this->deleteRecordByPk($record)) {
            return true;
        }

        $this->writeQueryBuilder->clear();
        $this->writeQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        foreach ($record as $key => $item) {
            if (isset($relations[$key]) && (false !== $item)) {
                $this->writeQueryBuilder->addCondition($record->getMapper()->getAlias(), $relations[$key], IQueryBuilder::OPERATION_EQ, $item);
            }
        }

        if (empty($this->writeQueryBuilder->getConditions())) { /// this one is necessary - delete everything? do it yourself - and manually!
            return false;
        }

        return $this->writeDatabase->exec(strval($this->writeDialect->delete($this->writeQueryBuilder)), $this->writeQueryBuilder->getParams());
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function deleteRecordByPk(ARecord $record): bool
    {
        if (empty($record->getMapper()->getPrimaryKeys())) {
            return false;
        }

        $this->writeQueryBuilder->clear();
        $this->writeQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        foreach ($record->getMapper()->getPrimaryKeys() as $key) {
            try {
                if (isset($relations[$key])) {
                    $item = $record->offsetGet($key);
                    if (false !== $item) {
                        $this->writeQueryBuilder->addCondition($record->getMapper()->getAlias(), $relations[$key], IQueryBuilder::OPERATION_EQ, $item);
                    }
                }
            } catch (MapperException $ex) {
                return false;
            }
        }

        if (empty($this->writeQueryBuilder->getConditions())) { // no conditions, nothing in PKs - back to normal system
            return false;
        }

        return $this->writeDatabase->exec(strval($this->writeDialect->delete($this->writeQueryBuilder)), $this->writeQueryBuilder->getParams());
    }

    public function loadMultiple(ARecord $record): array
    {
        $this->readQueryBuilder->clear();
        $this->readQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        foreach ($record as $key => $item) {
            if (isset($relations[$key]) && (false !== $item)) {
                $this->readQueryBuilder->addCondition($record->getMapper()->getAlias(), $relations[$key], IQueryBuilder::OPERATION_EQ, $item);
            }
        }

        // relations - what to get
        foreach ($relations as $localAlias => $remoteColumn) {
            $this->readQueryBuilder->addColumn($record->getMapper()->getAlias(), $remoteColumn, $localAlias);
        }

        // query itself
        $lines = $this->readDatabase->query(strval($this->readDialect->select($this->readQueryBuilder)), $this->readQueryBuilder->getParams());
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

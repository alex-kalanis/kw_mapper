<?php

namespace kalanis\kw_mapper\Mappers\Database;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Shared\TEntityChanged;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\TFill;
use kalanis\kw_mapper\Storage\Database;


/**
 * Trait TWriteDatabase
 * @package kalanis\kw_mapper\Mappers\Database
 * Separated write DB entry without need to reload mapper
 *
 * Contains only operations for writing into DB
 */
trait TWriteDatabase
{
    use TEntityChanged;
    use TFill;

    protected string $writeSource = '';
    protected Database\ASQL $writeDatabase;
    protected Database\Dialects\ADialect $writeDialect;
    protected Database\QueryBuilder $writeQueryBuilder;

    /**
     * @throws MapperException
     */
    public function initTWriteDatabase(): void
    {
        $this->writeDatabase = $this->getWriteDatabase();
        $this->writeDialect = $this->getWriteDialect($this->writeDatabase);
        $this->writeQueryBuilder = $this->getWriteQueryBuilder($this->writeDialect);
    }

    protected function setWriteSource(string $writeSource): void
    {
        $this->writeSource = $writeSource;
    }

    protected function getWriteSource(): string
    {
        return $this->writeSource;
    }

    /**
     * @throws MapperException
     * @return Database\ADatabase
     */
    protected function getWriteDatabase(): Database\ADatabase
    {
        return Database\DatabaseSingleton::getInstance()->getDatabase(
            Database\ConfigStorage::getInstance()->getConfig($this->getWriteSource())
        );
    }

    /**
     * @param Database\ADatabase $database
     * @throws MapperException
     * @return Database\Dialects\ADialect
     */
    protected function getWriteDialect(Database\ADatabase $database): Database\Dialects\ADialect
    {
        return Database\Dialects\Factory::getInstance()->getDialectClass($database->languageDialect());
    }

    protected function getWriteQueryBuilder(Database\Dialects\ADialect $dialect): Database\QueryBuilder
    {
        return new Database\QueryBuilder($dialect);
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function insertRecord(ARecord $record): bool
    {
        $this->writeQueryBuilder->clear();
        $this->writeQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        foreach ($record as $key => $item) {
            if (isset($relations[$key]) && $this->ifEntryChanged($record->getEntry($key))) {
                $this->writeQueryBuilder->addProperty(
                    $record->getMapper()->getAlias(),
                    $relations[$key],
                    $item
                );
            }
        }

        if (empty($this->writeQueryBuilder->getProperties())) {
            return false;
        }

        return $this->writeDatabase->exec(
            strval($this->writeDialect->insert($this->writeQueryBuilder)),
            $this->writeQueryBuilder->getParams()
        );
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function updateRecord(ARecord $record): bool
    {
        if ($this->updateRecordByPk($record)) {
            return true;
        }
        $this->writeQueryBuilder->clear();
        $this->writeQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        foreach ($record as $key => $item) {
            if (isset($relations[$key]) && $this->ifEntryChanged($record->getEntry($key))) {
                if ($record->getEntry($key)->isFromStorage()) {
                    $this->writeQueryBuilder->addCondition(
                        $record->getMapper()->getAlias(),
                        $relations[$key],
                        IQueryBuilder::OPERATION_EQ,
                        $item
                    );
                } else {
                    $this->writeQueryBuilder->addProperty(
                        $record->getMapper()->getAlias(),
                        $relations[$key],
                        $item
                    );
                }
            }
        }

        if (empty($this->writeQueryBuilder->getConditions())) { /// this one is questionable - I really want to update everything?
            return false;
        }
        if (empty($this->writeQueryBuilder->getProperties())) {
            return false;
        }

        return $this->writeDatabase->exec(
            strval($this->writeDialect->update($this->writeQueryBuilder)),
            $this->writeQueryBuilder->getParams()
        );
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
                    if ($entry->isFromStorage() && $this->ifEntryChanged($record->getEntry($key))) {
                        $this->writeQueryBuilder->addCondition(
                            $record->getMapper()->getAlias(),
                            $relations[$key],
                            IQueryBuilder::OPERATION_EQ,
                            $entry->getData()
                        );
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
                if (
                    !in_array($key, $record->getMapper()->getPrimaryKeys())
                    && !$entry->isFromStorage()
                    && $this->ifEntryChanged($record->getEntry($key))
                ) {
                    $this->writeQueryBuilder->addProperty($record->getMapper()->getAlias(), $relations[$key], $item);
                }
            }
        }

        if (empty($this->writeQueryBuilder->getProperties())) {
            return false;
        }

        return $this->writeDatabase->exec(
            strval($this->writeDialect->update($this->writeQueryBuilder)),
            $this->writeQueryBuilder->getParams()
        );
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function deleteRecord(ARecord $record): bool
    {
        if ($this->deleteRecordByPk($record)) {
            return true;
        }

        $this->writeQueryBuilder->clear();
        $this->writeQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        foreach ($record as $key => $item) {
            if (isset($relations[$key]) && $this->ifEntryChanged($record->getEntry($key))) {
                $this->writeQueryBuilder->addCondition(
                    $record->getMapper()->getAlias(),
                    $relations[$key],
                    IQueryBuilder::OPERATION_EQ,
                    $item
                );
            }
        }

        if (empty($this->writeQueryBuilder->getConditions())) { /// this one is necessary - delete everything? do it yourself - and manually!
            return false;
        }

        return $this->writeDatabase->exec(
            strval($this->writeDialect->delete($this->writeQueryBuilder)),
            $this->writeQueryBuilder->getParams()
        );
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
                if (isset($relations[$key]) && $this->ifEntryChanged($record->getEntry($key))) {
                    $this->writeQueryBuilder->addCondition(
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

        if (empty($this->writeQueryBuilder->getConditions())) { // no conditions, nothing in PKs - back to normal system
            return false;
        }

        return $this->writeDatabase->exec(
            strval($this->writeDialect->delete($this->writeQueryBuilder)),
            $this->writeQueryBuilder->getParams()
        );
    }
}

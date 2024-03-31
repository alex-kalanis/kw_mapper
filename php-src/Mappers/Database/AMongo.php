<?php

namespace kalanis\kw_mapper\Mappers\Database;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\AMapper;
use kalanis\kw_mapper\Mappers\Shared\TEntityChanged;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\TFill;
use kalanis\kw_mapper\Storage\Database;
use kalanis\kw_mapper\Storage\Shared;


/**
 * Class AMongo
 * @package kalanis\kw_mapper\Mappers\Database
 * Mapper to NoSQL database MongoDB
 * @codeCoverageIgnore for now - external source
 */
abstract class AMongo extends AMapper
{
    use TEntityChanged;
    use TFill;
    use TTable;

    protected ?string $readSource = null;
    protected ?string $writeSource = null;
    protected Database\Raw\MongoDb $readDatabase;
    protected Database\Raw\MongoDb $writeDatabase;
    protected Database\Dialects\MongoDb $readDialect;
    protected Database\Dialects\MongoDb $writeDialect;
    protected Shared\QueryBuilder $readQueryBuilder;
    protected Shared\QueryBuilder $writeQueryBuilder;

    /**
     * @throws MapperException
     */
    public function __construct()
    {
        parent::__construct();

        $this->initReadDatabase();
        $this->initWriteDatabase();
    }

    /**
     * @throws MapperException
     */
    protected function initReadDatabase(): void
    {
        $this->readDatabase = $this->getReadDatabase();
        $this->readDialect = $this->getReadDialect($this->readDatabase);
        $this->readQueryBuilder = $this->getReadQueryBuilder();
    }

    /**
     * @throws MapperException
     */
    public function initWriteDatabase(): void
    {
        $this->writeDatabase = $this->getWriteDatabase();
        $this->writeDialect = $this->getWriteDialect($this->writeDatabase);
        $this->writeQueryBuilder = $this->getWriteQueryBuilder();
    }

    protected function setReadSource(string $readSource): void
    {
        $this->readSource = $readSource;
    }

    protected function getReadSource(): string
    {
        return $this->readSource ?? $this->getSource();
    }

    protected function setWriteSource(string $writeSource): void
    {
        $this->writeSource = $writeSource;
    }

    protected function getWriteSource(): string
    {
        return $this->writeSource ?? $this->getSource();
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

    protected function getReadQueryBuilder(): Shared\QueryBuilder
    {
        return new Shared\QueryBuilder();
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

    protected function getWriteQueryBuilder(): Shared\QueryBuilder
    {
        return new Shared\QueryBuilder();
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
            $this->writeQueryBuilder,
            // @phpstan-ignore-next-line  expects MongoDB\Driver\BulkWrite, object|string given
            $this->writeDialect->insert($this->writeQueryBuilder)
        );
    }

    protected function updateRecord(ARecord $record): bool
    {
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
            $this->writeQueryBuilder,
            // @phpstan-ignore-next-line  expects MongoDB\Driver\BulkWrite, object|string given
            $this->writeDialect->update($this->writeQueryBuilder)
        );
    }

    protected function deleteRecord(ARecord $record): bool
    {
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
            $this->writeQueryBuilder,
            // @phpstan-ignore-next-line  expects MongoDB\Driver\BulkWrite, object|string given
            $this->writeDialect->delete($this->writeQueryBuilder)
        );
    }

    protected function loadRecord(ARecord $record): bool
    {
        $this->fillConditions($record);
        $lines = $this->multiple();
        if (empty($lines) || empty($lines[0]) || !is_iterable($lines[0])) { // nothing found
            return false;
        }

        // fill entries in record
        $relations = $record->getMapper()->getRelations();
        $relationMap = array_flip($relations);
        foreach ($lines[0] as $index => $item) {
            $entry = $record->getEntry($relationMap[$index]);
            $entry->setData($this->typedFillSelection($entry, $item), true);
        }
        return true;
    }

    public function countRecord(ARecord $record): int
    {
        $this->fillConditions($record);
        return count($this->multiple());
    }

    public function loadMultiple(ARecord $record): array
    {
        $this->fillConditions($record);
        $lines = $this->multiple();
        if (empty($lines)) {
            return [];
        }

        $result = [];
        $relations = $record->getMapper()->getRelations();
        $relationMap = array_flip($relations);
        foreach ($lines as $key => $line) {
            if (is_numeric($key) && is_iterable($line)) {
                $rec = clone $record;
                foreach ($line as $index => $item) {
                    $entry = $rec->getEntry($relationMap[$index]);
                    $entry->setData($this->typedFillSelection($entry, $item), true);
                }
                $result[] = $rec;
            }
        }
        return $result;
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     */
    protected function fillConditions(ARecord $record): void
    {
        $this->readQueryBuilder->clear();
        $this->readQueryBuilder->setBaseTable($record->getMapper()->getAlias());
        $relations = $record->getMapper()->getRelations();

        foreach ($record as $key => $item) {
            if (!$record->getEntry($key)->isFromStorage() && $this->ifEntryChanged($record->getEntry($key))) {
                $this->readQueryBuilder->addCondition(
                    $record->getMapper()->getAlias(),
                    $relations[$key],
                    IQueryBuilder::OPERATION_EQ,
                    strval($item)
                );
            }
        }
    }

    /**
     * @throws MapperException
     * @return array<string|int, string|int|float|array<string|int|float>>
     */
    protected function multiple(): array
    {
        return $this->readDatabase->query(
            $this->readQueryBuilder,
            // @phpstan-ignore-next-line  expects MongoDB\Driver\Query, object|string given
            $this->readDialect->select($this->readQueryBuilder)
        )->toArray();
    }
}

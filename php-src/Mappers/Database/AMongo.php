<?php

namespace kalanis\kw_mapper\Mappers\Database;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\AMapper;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\TFill;
use kalanis\kw_mapper\Storage;


/**
 * Class AMongo
 * @package kalanis\kw_mapper\Mappers\Database
 * Mapper to NoSQL database MongoDB
 * @codeCoverageIgnore for now - external source
 */
abstract class AMongo extends AMapper
{
    use TFill;
    use TTable;

    /** @var Storage\Database\Raw\MongoDb */
    protected $database = null;
    /** @var Storage\Shared\QueryBuilder|null */
    protected $queryBuilder = null;
    /** @var Storage\Database\Dialects\MongoDb */
    protected $dialect = null;

    /**
     * @throws MapperException
     */
    public function __construct()
    {
        parent::__construct();
        $config = Storage\Database\ConfigStorage::getInstance()->getConfig($this->getSource());
        $this->database = Storage\Database\DatabaseSingleton::getInstance()->getDatabase($config);
        $this->dialect = Storage\Database\Dialects\Factory::getInstance()->getDialectClass($this->database->languageDialect());
        $this->queryBuilder = new Storage\Shared\QueryBuilder();
    }

    public function getAlias(): string
    {
        return $this->getTable();
    }

    protected function insertRecord(ARecord $record): bool
    {
        $this->queryBuilder->clear();
        $this->queryBuilder->setBaseTable($this->getTable());
        foreach ($record as $key => $item) {
            $this->queryBuilder->addProperty($this->getTable(), $this->relations[$key], $item);
        }
        // @phpstan-ignore-next-line
        return $this->database->exec($this->queryBuilder, $this->dialect->insert($this->queryBuilder));
    }

    protected function updateRecord(ARecord $record): bool
    {
        $this->queryBuilder->clear();
        $this->queryBuilder->setBaseTable($this->getTable());
        foreach ($record as $key => $item) {
            if (false !== $item) {
                if ($record->getEntry($key)->isFromStorage()) {
                    $this->queryBuilder->addCondition($this->getTable(), $this->relations[$key], IQueryBuilder::OPERATION_EQ, $item);
                } else {
                    $this->queryBuilder->addProperty($this->getTable(), $this->relations[$key], $item);
                }
            }
        }
        // @phpstan-ignore-next-line
        return $this->database->exec($this->queryBuilder, $this->dialect->update($this->queryBuilder));
    }

    protected function deleteRecord(ARecord $record): bool
    {
        $this->queryBuilder->clear();
        $this->queryBuilder->setBaseTable($this->getTable());
        foreach ($record as $key => $item) {
            if (false !== $item) {
                $this->queryBuilder->addCondition($this->getTable(), $this->relations[$key], IQueryBuilder::OPERATION_EQ, $item);
            }
        }
        // @phpstan-ignore-next-line
        return $this->database->exec($this->queryBuilder, $this->dialect->delete($this->queryBuilder));
    }

    protected function loadRecord(ARecord $record): bool
    {
        $this->fillConditions($record);
        $lines = $this->multiple();
        if (empty($lines) || empty($lines[0]) || !is_iterable($lines[0])) { // nothing found
            return false;
        }

        // fill entries in record
        $relationMap = array_flip($this->relations);
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
        $relationMap = array_flip($this->relations);
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
        $this->queryBuilder->clear();
        $this->queryBuilder->setBaseTable($this->getTable());
        foreach ($record as $key => $item) {
            if ((false !== $item) && !$record->getEntry($key)->isFromStorage()) {
                $this->queryBuilder->addCondition($this->getTable(), $this->relations[$key], strval($item));
            }
        }
    }

    /**
     * @throws MapperException
     * @return array<string|int, string|int|float|array<string|int|float>>
     */
    protected function multiple(): array
    {
        // @phpstan-ignore-next-line
        return $this->database->query($this->queryBuilder, $this->dialect->select($this->queryBuilder))->toArray();
    }
}

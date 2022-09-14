<?php

namespace kalanis\kw_mapper\Search\Connector;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Database;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\TFill;
use kalanis\kw_mapper\Storage;


/**
 * Class WinRegistry
 * @package kalanis\kw_mapper\Search
 * Windows Registry - connect them as datasource
 * Allow to walk through the registry tree
 * @todo:
 * Operations with QueryBuilder
 * @codeCoverageIgnore for now - external source
 */
class WinRegistry extends AConnector
{
    use TFill;

    /** @var Storage\Database\Raw\WinRegistry */
    protected $database = null;

    /**
     * @param ARecord $record
     * @param Storage\Shared\QueryBuilder|null $builder
     * @throws MapperException
     */
    public function __construct(ARecord $record, ?Storage\Shared\QueryBuilder $builder = null)
    {
        $this->basicRecord = $record;
        $this->initRecordLookup($record);
        $config = Storage\Database\ConfigStorage::getInstance()->getConfig($record->getMapper()->getSource());
        $this->database = Storage\Database\DatabaseSingleton::getInstance()->getDatabase($config);
        $this->queryBuilder = $builder ?: new Storage\Shared\QueryBuilder();
        $this->queryBuilder->setBaseTable($record->getMapper()->getAlias());
    }

    /**
     * Return only number of subtrees
     * @throws MapperException
     * @return int
     */
    public function getCount(): int
    {
        $targets = $this->multiple();
        return count($targets);
    }

    /**
     * Returns only keys; values are available via ARecord->loadMultiple()
     * @throws MapperException
     * @return ARecord[]
     */
    public function getResults(): array
    {
        $targets = $this->multiple();

        if (empty($targets)) {
            return [];
        }

        $result = [];
        /** @var Database\WinRegistry|Database\WinRegistry2 $mapper */
        $mapper = $this->basicRecord->getMapper();
        $pks = $this->basicRecord->getMapper()->getPrimaryKeys();
        reset($pks);
        $pathEntry = next($pks);

        foreach ($targets as $path) {
            $rec = clone $this->basicRecord;

            $entry = $rec->getEntry(strval($pathEntry));
            $entry->setData($this->typedFillSelection($entry, $path), true);
            $rec->offsetSet($mapper->getTypeKey(), '');
            $rec->offsetSet($mapper->getContentKey(), '');

            $result[] = $rec;
        }
        return $result;
    }

    /**
     * @throws MapperException
     * @return string[][]
     */
    protected function multiple(): array
    {
        $pks = $this->basicRecord->getMapper()->getPrimaryKeys();
        return $this->database->subtree(
            $this->basicRecord->offsetGet(reset($pks)),
            $this->basicRecord->offsetGet(next($pks))
        );
    }
}

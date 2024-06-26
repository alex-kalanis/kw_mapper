<?php

namespace kalanis\kw_mapper\Search\Connector\Database;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use ReflectionClass;
use ReflectionException;


/**
 * Trait TRecordsInJoins
 * @package kalanis\kw_mapper\Search\Connector\Database
 * Which records are in selection
 */
trait TRecordsInJoins
{
    /** @var RecordsInJoin[] */
    protected array $recordsInJoin = [];

    /**
     * @param ARecord $record
     * @throws MapperException
     */
    public function initRecordLookup(ARecord $record): void
    {
        $rec = new RecordsInJoin();
        $rec->setData(
            $record,
            $record->getMapper()->getAlias(),
            null,
            ''
        );
        $this->recordsInJoin[$record->getMapper()->getAlias()] = $rec;
    }

    /**
     * @param string $storeKey
     * @param string $knownAs
     * @throws MapperException
     * @return RecordsInJoin|null
     */
    public function recordLookup(string $storeKey, string $knownAs = ''): ?RecordsInJoin
    {
        if (isset($this->recordsInJoin[$storeKey])) {
            return $this->recordsInJoin[$storeKey];
        }
        foreach ($this->recordsInJoin as $record) {
            $foreignKeys = $record->getRecord()->getMapper()->getForeignKeys();
            $fk = empty($knownAs) ? $storeKey : $knownAs ;
            if (isset($foreignKeys[$fk])) {
                $recordClassName = $foreignKeys[$fk]->getRemoteRecord();
                try {
                    /** @var class-string $recordClassName */
                    $reflect = new ReflectionClass($recordClassName);
                    $thatRecord = $reflect->newInstance();
                } catch (ReflectionException $ex) {
                    throw new MapperException($ex->getMessage(), $ex->getCode(), $ex);
                }
                /** @var ARecord $thatRecord */
                $rec = new RecordsInJoin();
                $rec->setData(
                    $thatRecord,
                    $storeKey,
                    $record->getRecord()->getMapper()->getAlias(),
                    $knownAs
                );
                $this->recordsInJoin[$storeKey] = $rec;
                return $this->recordsInJoin[$storeKey];
            }
        }
        return null;
    }

    /**
     * @return RecordsInJoin[]
     */
    public function getRecordsInJoin(): array
    {
        return $this->recordsInJoin;
    }
}

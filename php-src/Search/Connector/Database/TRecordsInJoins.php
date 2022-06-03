<?php

namespace kalanis\kw_mapper\Search\Connector\Database;


use kalanis\kw_mapper\Records\ARecord;


/**
 * Trait TRecordsInJoins
 * @package kalanis\kw_mapper\Search\Connector\Database
 * Which records are in selection
 */
trait TRecordsInJoins
{
    /** @var RecordsInJoin[] */
    protected $recordsInJoin = [];

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
                $thatRecord = new $recordClassName();
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

    public function getRecordsInJoin(): array
    {
        return $this->recordsInJoin;
    }
}

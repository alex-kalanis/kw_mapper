<?php

namespace kalanis\kw_mapper\Search\Connector\Database;


use kalanis\kw_mapper\Records\ARecord;


/**
 * Trait TRecords
 * @package kalanis\kw_mapper\Search\Connector\Database
 * Which records are in selection
 */
trait TRecords
{
    /** @var Records[] */
    protected $records = [];

    public function initRecordLookup(ARecord $record): void
    {
        $rec = new Records();
        $rec->setData(
            $record,
            $record->getMapper()->getAlias(),
            null,
            ''
        );
        $this->records[$record->getMapper()->getAlias()] = $rec;
    }

    public function recordLookup(string $storeKey, string $knownAs = ''): ?Records
    {
        if (isset($this->records[$storeKey])) {
            return $this->records[$storeKey];
        }
        foreach ($this->records as $record) {
            $foreignKeys = $record->getRecord()->getMapper()->getForeignKeys();
            $fk = empty($knownAs) ? $storeKey : $knownAs ;
            if (isset($foreignKeys[$fk])) {
                $recordClassName = $foreignKeys[$fk]->getRemoteRecord();
                $thatRecord = new $recordClassName();
                $rec = new Records();
                $rec->setData(
                    $thatRecord,
                    $storeKey,
                    $record->getRecord()->getMapper()->getAlias(),
                    $knownAs
                );
                $this->records[$storeKey] = $rec;
                return $this->records[$storeKey];
            }
        }
        return null;
    }

    public function getRecords(): array
    {
        return $this->records;
    }
}

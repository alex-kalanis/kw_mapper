<?php

namespace kalanis\kw_mapper\Mappers\Storage;


use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Storage\Shared\FormatFiles\SinglePage;
use kalanis\kw_storage\StorageException;


/**
 * Class KeyValue
 * @package kalanis\kw_mapper\Mappers\Storage
 * Key-value pairs somewhere in storage mapped for extra usage
 * It can be a file, it can be an entry in Redis, Memcache or other sources
 */
class KeyValue extends AFile
{
    protected function setMap(): void
    {
        $this->setStorage();
        $this->setPathKey('key');
        $this->setContentKey('content');
        $this->setFormat(SinglePage::class);
    }

    public function loadMultiple(ARecord $record): array
    {
        $inPath = $this->getPathFromPk($record);
        $path = strval($record->offsetGet($inPath));
        $records = [];
        try {
            foreach ($this->getStorage()->lookup($path) as $contentKey) {
                $rec = clone $record;
                $rec->offsetSet($inPath, $path . $contentKey);
                $rec->load();
                $records[] = $rec;
            }
        } catch (StorageException $ex) {
            return [];
        }
        return $records;
    }
}

<?php

namespace kalanis\kw_mapper\Mappers\Storage;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Shared\TFile;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\PageRecord;
use kalanis\kw_storage\StorageException;


/**
 * Class AFile
 * @package kalanis\kw_mapper\Mappers\Storage
 * Abstract layer for working with single files as content source
 */
abstract class AFile extends AStorage
{
    use TFile;

    /**
     * @param ARecord|PageRecord $record
     * @throws MapperException
     * @return bool
     */
    protected function deleteRecord(ARecord $record): bool
    {
        $path = strval($record->offsetGet($this->getPathFromPk($record)));
        try {
            if ($this->getStorage()->exists($path)) {
                return $this->getStorage()->remove($path);
            }
        } catch (StorageException $ex) {
            return false;
        }
        return true; // not found - operation successful
    }
}

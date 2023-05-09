<?php

namespace kalanis\kw_mapper\Mappers\File;


use kalanis\kw_files\FilesException;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Shared\TFile;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\PageRecord;
use kalanis\kw_paths\PathsException;


/**
 * Class AFile
 * @package kalanis\kw_mapper\Mappers\File
 * Abstract layer for working with single files as content source
 */
abstract class AFile extends AFileSource
{
    use TFile;

    /**
     * @param ARecord|PageRecord $record
     * @throws MapperException
     * @return bool
     */
    protected function deleteRecord(ARecord $record): bool
    {
        try {
            return $this->getFileAccessor()->deleteFile($this->getPath());
        } catch (FilesException | PathsException $ex) {
            return false;
        }
    }
}

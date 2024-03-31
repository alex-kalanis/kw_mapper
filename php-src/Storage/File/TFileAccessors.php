<?php

namespace kalanis\kw_mapper\Storage\File;


use kalanis\kw_files\Interfaces\IProcessFiles;
use kalanis\kw_mapper\MapperException;


/**
 * Trait TFileAccessors
 * @package kalanis\kw_mapper\Storage\File
 */
trait TFileAccessors
{
    use TFile;

    protected function setFileAccessor(?IProcessFiles $file): void
    {
        FilesSingleton::getInstance()->setFileAccessor($file);
    }

    /**
     * @throws MapperException
     * @return IProcessFiles
     */
    protected function getFileAccessor(): IProcessFiles
    {
        return FilesSingleton::getInstance()->getFilesAccessor();
    }
}

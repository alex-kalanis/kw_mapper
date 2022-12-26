<?php

namespace kalanis\kw_mapper\Storage\File;


use kalanis\kw_files\Interfaces\IProcessFiles;
use kalanis\kw_mapper\MapperException;


/**
 * Trait TFile
 * @package kalanis\kw_mapper\Storage\File
 */
trait TFile
{
    /** @var string[] */
    protected $presetPath = [];

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

    /**
     * @param string[] $path
     */
    protected function setPath(array $path): void
    {
        $this->presetPath = $path;
    }

    /**
     * @return string[]
     */
    protected function getPath(): array
    {
        return $this->presetPath;
    }
}

<?php

namespace kalanis\kw_mapper\Storage\File;


use kalanis\kw_files\Interfaces\IProcessFiles;
use kalanis\kw_mapper\MapperException;


/**
 * Class FilesSingleton
 * @package kalanis\kw_mapper\Storage\File
 * Singleton to access files across the mappers
 */
class FilesSingleton
{
    protected static ?FilesSingleton $instance = null;
    private ?IProcessFiles $filesAccessor = null;

    public static function getInstance(): self
    {
        if (empty(static::$instance)) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    protected function __construct()
    {
    }

    /**
     * @codeCoverageIgnore why someone would run that?!
     */
    private function __clone()
    {
    }

    public function setFileAccessor(?IProcessFiles $files): void
    {
        $this->filesAccessor = $files;
    }

    /**
     * @throws MapperException
     * @return IProcessFiles
     */
    public function getFilesAccessor(): IProcessFiles
    {
        if (empty($this->filesAccessor)) {
            throw new MapperException('You must set the files accessor - instance of *IProcessFiles* - first!');
        }
        return $this->filesAccessor;
    }
}

<?php

namespace kalanis\kw_mapper\Storage\File;


use kalanis\kw_mapper\Interfaces\IFileFormat;


/**
 * Class ContentEntity
 * @package kalanis\kw_mapper\Storage\File
 */
class ContentEntity
{
    /** @var IFileFormat|null */
    protected $formatClass = null;
    protected $storage = [];

    public function __construct(IFileFormat $formatClass)
    {
        $this->formatClass = $formatClass;
    }

    /**
     * @codeCoverageIgnore why someone would run that?!
     */
    private function __clone()
    {
    }

    public function get(): array
    {
        return $this->storage;
    }

    public function set(array $content): void
    {
        $this->storage = $content;
    }

    public function getFormat(): IFileFormat
    {
        return $this->formatClass;
    }
}

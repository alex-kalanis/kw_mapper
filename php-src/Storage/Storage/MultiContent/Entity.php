<?php

namespace kalanis\kw_mapper\Storage\Storage\MultiContent;


use kalanis\kw_mapper\Interfaces\IFileFormat;


/**
 * Class Entity
 * @package kalanis\kw_mapper\Storage\Storage\MultiContent
 */
class Entity
{
    /** @var IFileFormat|null */
    protected $formatClass = null;
    /** @var string[] */
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

    /**
     * @return string[]
     */
    public function get(): array
    {
        return $this->storage;
    }

    /**
     * @param string[] $content
     */
    public function set(array $content): void
    {
        $this->storage = $content;
    }

    public function getFormat(): ?IFileFormat
    {
        return $this->formatClass;
    }
}

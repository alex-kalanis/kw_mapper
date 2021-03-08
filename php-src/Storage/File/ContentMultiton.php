<?php

namespace kalanis\kw_mapper\Storage\File;


use kalanis\kw_mapper\Interfaces\IFileFormat;
use kalanis\kw_mapper\MapperException;


/**
 * Class ContentMultiton
 * @package kalanis\kw_mapper\Storage\File
 * Content is stored as array of ContentEntities where first key is usually file name
 * You also need to specify the format in which is it stored
 */
class ContentMultiton
{
    protected static $instance = null;
    /** @var ContentEntity[] */
    private $storage = [];

    public static function getInstance(): self
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
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

    public function init(string $key, IFileFormat $formatClass): void
    {
        $this->storage[$key] = new ContentEntity($formatClass);
    }

    public function known(string $key): bool
    {
        return isset($this->storage[$key]);
    }

    /**
     * @param string $key
     * @return string[]
     * @throws MapperException
     */
    public function getContent(string $key): array
    {
        $this->checkContent($key);
        return $this->storage[$key]->get();
    }

    /**
     * @param string $key
     * @return IFileFormat
     * @throws MapperException
     */
    public function getFormatClass(string $key): IFileFormat
    {
        $this->checkContent($key);
        return $this->storage[$key]->getFormat();
    }

    /**
     * @param string $key
     * @param string[] $content
     * @throws MapperException
     */
    public function setContent(string $key, array $content): void
    {
        $this->checkContent($key);
        $this->storage[$key]->set($content);
    }

    /**
     * @param string $key
     * @throws MapperException
     */
    protected function checkContent(string $key): void
    {
        if (!$this->known($key)) {
            throw new MapperException('Uninitialized content');
        }
    }
}

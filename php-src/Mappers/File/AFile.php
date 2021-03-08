<?php

namespace kalanis\kw_mapper\Mappers\File;


use kalanis\kw_mapper\Interfaces\IFileFormat;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\AMapper;
use kalanis\kw_mapper\Storage\File;
use kalanis\kw_storage\StorageException;


/**
 * Class AFile
 * @package kalanis\kw_mapper\Mappers\Database
 * The path is separated
 * - storage has first half which is usually static
 * - content has second half which can be changed by circumstances
 */
abstract class AFile extends AMapper
{
    use File\TStorage;

    protected $processPath = '';

    protected $format = '';

    public function getAlias(): string
    {
        return $this->getFile();
    }

    public function setFile(string $file): self
    {
        $this->processPath = $file;
        return $this;
    }

    public function getFile(): string
    {
        return $this->processPath;
    }

    public function setFormat(string $formatClass): self
    {
        $this->format = $formatClass;
        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @throws MapperException
     */
    protected function loadCached(): void
    {
        $storage = File\ContentMultiton::getInstance();
        $this->initLocalStorage($storage);
        $storage->setContent($this->getFile(), $this->loadFromRemoteSource($storage->getFormatClass($this->getFile())));
    }

    /**
     * @param IFileFormat|null $format
     * @return array
     * @throws MapperException
     */
    protected function loadFromRemoteSource(?IFileFormat $format = null): array
    {
        try {
            $format = $format ?? File\Formats\Factory::getInstance()->getFormatClass($this->getFormat());
            return $format->unpack($this->getStorage()->read($this->getFile()));
        } catch (StorageException $ex) {
            throw new MapperException('Unable to read source', 0, $ex);
        }
    }

    /**
     * @return bool
     * @throws MapperException
     */
    protected function saveCached(): bool
    {
        $storage = File\ContentMultiton::getInstance();
        $this->initLocalStorage($storage);
        return $this->saveToRemoteSource($storage->getContent($this->getFile()), $storage->getFormatClass($this->getFile()));
    }

    /**
     * @param array $content
     * @param IFileFormat|null $format
     * @return bool
     * @throws MapperException
     */
    protected function saveToRemoteSource(array $content, ?IFileFormat $format = null): bool
    {
        try {
            $format = $format ?? File\Formats\Factory::getInstance()->getFormatClass($this->getFormat());
            return $this->getStorage()->write($this->getFile(), $format->pack($content));
        } catch (StorageException $ex) {
            throw new MapperException('Unable to write into source', 0, $ex);
        }
    }

    /**
     * @param File\ContentMultiton $storage
     * @throws MapperException
     */
    protected function initLocalStorage(File\ContentMultiton $storage): void
    {
        if (!$storage->known($this->getFile())) {
            $pack = File\Formats\Factory::getInstance();
            $storage->init($this->getFile(), $pack->getFormatClass($this->getFormat()));
        }
    }
}

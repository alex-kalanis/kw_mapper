<?php

namespace kalanis\kw_mapper\Mappers\File;


use kalanis\kw_mapper\Interfaces\IFileFormat;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\AMapper;
use kalanis\kw_mapper\Storage\File;
use kalanis\kw_storage\StorageException;


/**
 * Class AStorage
 * @package kalanis\kw_mapper\Mappers\Database
 * The path is separated
 * - storage has first half which is usually static
 * - content has second half which can be changed by circumstances
 */
abstract class AStorage extends AMapper
{
    use File\TStorage;
    use File\TFormat;

    public function getAlias(): string
    {
        return $this->getSource();
    }

    /**
     * @param IFileFormat|null $format
     * @return array
     * @throws MapperException
     */
    protected function loadFromStorage(?IFileFormat $format = null): array
    {
        try {
            $format = $format ?: File\Formats\Factory::getInstance()->getFormatClass($this->getFormat());
            return $format->unpack($this->getStorage()->read($this->getSource()));
        } catch (StorageException $ex) {
            throw new MapperException('Unable to read from source', 0, $ex);
        }
    }

    /**
     * @param array $content
     * @param IFileFormat|null $format
     * @return bool
     * @throws MapperException
     */
    protected function saveToStorage(array $content, ?IFileFormat $format = null): bool
    {
        try {
            $format = $format ?: File\Formats\Factory::getInstance()->getFormatClass($this->getFormat());
            return $this->getStorage()->write($this->getSource(), $format->pack($content));
        } catch (StorageException $ex) {
            throw new MapperException('Unable to write into source', 0, $ex);
        }
    }
}

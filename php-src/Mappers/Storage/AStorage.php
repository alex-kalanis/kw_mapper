<?php

namespace kalanis\kw_mapper\Mappers\Storage;


use kalanis\kw_mapper\Interfaces\IFileFormat;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\AMapper;
use kalanis\kw_mapper\Storage\Shared;
use kalanis\kw_mapper\Storage\Storage;
use kalanis\kw_storage\StorageException;


/**
 * Class AStorage
 * @package kalanis\kw_mapper\Mappers\Storage
 * The path is separated
 * - storage has first half which is usually static
 * - content has second half which can be changed by circumstances
 */
abstract class AStorage extends AMapper
{
    use Storage\TStorage;
    use Shared\TFormat;

    public function getAlias(): string
    {
        return $this->getSource();
    }

    protected function getReadSource(): string
    {
        return $this->getSource();
    }

    protected function getWriteSource(): string
    {
        return $this->getSource();
    }

    /**
     * @param IFileFormat|null $format
     * @throws MapperException
     * @return array<string|int, array<string|int, string|int|float|bool|array<string|int, string|int|float|bool>>>
     */
    protected function loadFromStorage(?IFileFormat $format = null): array
    {
        try {
            $format = $format ?: Shared\FormatFiles\Factory::getInstance()->getFormatClass($this->getFormat());
            return $format->unpack($this->getStorage()->read($this->getReadSource()));
        } catch (StorageException $ex) {
            throw new MapperException('Unable to read from source', 0, $ex);
        }
    }

    /**
     * @param array<string|int, array<string|int, string|int|float|bool|array<string|int, string|int|float|bool>>> $content
     * @param IFileFormat|null $format
     * @throws MapperException
     * @return bool
     */
    protected function saveToStorage(array $content, ?IFileFormat $format = null): bool
    {
        try {
            $format = $format ?: Shared\FormatFiles\Factory::getInstance()->getFormatClass($this->getFormat());
            return $this->getStorage()->write($this->getWriteSource(), $format->pack($content));
        } catch (StorageException $ex) {
            throw new MapperException('Unable to write into source', 0, $ex);
        }
    }
}

<?php

namespace kalanis\kw_mapper\Mappers\File;


use kalanis\kw_files\FilesException;
use kalanis\kw_mapper\Interfaces\IFileFormat;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\AMapper;
use kalanis\kw_mapper\Storage\File;
use kalanis\kw_mapper\Storage\Shared;
use kalanis\kw_paths\PathsException;


/**
 * Class AFileSource
 * @package kalanis\kw_mapper\Mappers\File
 * The path is separated
 * - files (sometimes even with the storage underneath) has first half which is usually static
 * - content has second half which can be changed by circumstances
 */
abstract class AFileSource extends AMapper
{
    use File\TFile;
    use Shared\TFormat;

    /**
     * @param string[] $path
     */
    public function setCombinedPath(array $path): void
    {
        $this->setPath($path);
        $this->setSource(implode($this->getCombinedSourceSeparator(), $path));
    }

    protected function getCombinedSourceSeparator(): string
    {
        return "//\e\e//";
    }

    public function getAlias(): string
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
            $content = $this->getFileAccessor()->readFile($this->getPath());
            if (is_resource($content)) {
                rewind($content);
                $content = stream_get_contents($content, -1, 0);
                if (false === $content) {
                    // @codeCoverageIgnoreStart
                    throw new MapperException('Cannot read stream resource from file storage');
                }
                // @codeCoverageIgnoreEnd
            }
            return $format->unpack($content);
        } catch (FilesException | PathsException $ex) {
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
            return $this->getFileAccessor()->saveFile($this->getPath(), $format->pack($content));
        } catch (FilesException | PathsException $ex) {
            throw new MapperException('Unable to write into source', 0, $ex);
        }
    }
}

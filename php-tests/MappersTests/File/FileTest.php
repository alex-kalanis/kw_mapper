<?php

namespace MappersTests\File;


use CommonTestClass;
use kalanis\kw_files\FilesException;
use kalanis\kw_files\Interfaces\IProcessFiles;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Storage\Shared\FormatFiles;


class FileTest extends CommonTestClass
{
    public function testContentOk(): void
    {
        $path = $this->getTestFile1();
        $lib = new Mappers\File\PageContent();
        $lib->setSource($path);
        $this->assertEquals($path, $lib->getAlias());
    }

    /**
     * @throws MapperException
     */
    public function testContentOkLoad(): void
    {
        $rec = new XPageContent();
        $rec->key = $this->getTestFile1();
        $this->assertTrue($rec->load());
        $this->assertNotEmpty($rec->loadMultiple());
    }

    /**
     * @throws MapperException
     */
    public function testContentOkSave(): void
    {
        $rec = new XPageContent();
        $rec->key = $this->getTestFile1();
        $rec->content = 'okmijnuhbzgvtfcrdxesy';
        $this->assertTrue($rec->save());
    }

    /**
     * @throws MapperException
     */
    public function testLoad(): void
    {
        $rec = new XPageContent();
        $rec->key = $this->getTestFile1();

        $lib = new XPageContentMapper();
        $lib->passOnTest();
        $this->assertNotEmpty($lib->load($rec));
        $lib->setCombinedPath($this->getTestFile2());
        $this->assertEquals($this->getTestFile2(), $lib->getExtPath());

        $lib->dieOnTest();
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Unable to read from source');
        $lib->load($rec);
    }

    /**
     * @throws MapperException
     */
    public function testSave(): void
    {
        $rec = new XPageContent();
        $rec->key = $this->getTestFile1();
        $rec->content = 'okmijnuhbzgvtfcrdxesy';

        $lib = new XPageContentMapper();
        $lib->passOnTest();
        $this->assertTrue($lib->save($rec));

        $lib->dieOnTest();
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Unable to write into source');
        $lib->save($rec);
    }

    /**
     * @throws MapperException
     */
    public function testDelete(): void
    {
        $rec = new XPageContent();
        $rec->key = $this->getTestFile1();
        $rec->content = 'okmijnuhbzgvtfcrdxesy';

        $lib = new XPageContentMapper();
        $lib->passOnTest();
        $this->assertTrue($lib->delete($rec));

        $lib->dieOnTest();
        $this->assertFalse($lib->delete($rec));
    }

    protected function getTestFile1(): string
    {
        return 'fileTest.txt';
    }

    protected function getTestFile2(): array
    {
        return ['fileTest.txt'];
    }
}


class XAccessFilePass implements IProcessFiles
{
    public function findFreeName(array $path, string $name, string $suffix): string
    {
        return '';
    }

    public function saveFile(array $entry, string $content, ?int $offset = null, int $mode = 0): bool
    {
        return true;
    }

    public function readFile(array $entry, ?int $offset = null, ?int $length = null): string
    {
        return 'testing string 1234567890abcdefghijklmnopqrstuvwxyz';
    }

    public function copyFile(array $source, array $dest): bool
    {
        return true;
    }

    public function moveFile(array $source, array $dest): bool
    {
        return true;
    }

    public function deleteFile(array $entry): bool
    {
        return true;
    }
}


class XAccessFileDie implements IProcessFiles
{
    public function findFreeName(array $path, string $name, string $suffix): string
    {
        return '';
    }

    public function saveFile(array $entry, string $content, ?int $offset = null, int $mode = 0): bool
    {
        throw new FilesException('Cannot access here');
    }

    public function readFile(array $entry, ?int $offset = null, ?int $length = null): string
    {
        throw new FilesException('Cannot access here');
    }

    public function copyFile(array $source, array $dest): bool
    {
        return false;
    }

    public function moveFile(array $source, array $dest): bool
    {
        return false;
    }

    public function deleteFile(array $entry): bool
    {
        throw new FilesException('Cannot access here');
    }
}


class XPageContentMapper extends Mappers\File\PageContent
{
    protected function setMap(): void
    {
        $this->setPathKey('key');
        $this->setContentKey('content');
        $this->setFormat(FormatFiles\SinglePage::class);
        $this->setFileAccessor(new XAccessFilePass());
    }

    public function passOnTest(): void
    {
        $this->setFileAccessor(new XAccessFilePass());
    }

    public function dieOnTest(): void
    {
        $this->setFileAccessor(new XAccessFileDie());
    }

    public function getExtPath(): array
    {
        return $this->getPath();
    }
}


/**
 * Class XPageContent
 * @package MappersTests\Storage
 * @property string $key
 * @property string $content
 */
class XPageContent extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('key', IEntryType::TYPE_STRING, 512);
        $this->addEntry('content', IEntryType::TYPE_STRING, PHP_INT_MAX);
        $this->setMapper(XPageContentMapper::class);
    }
}

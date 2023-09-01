<?php

namespace MappersTests\Storage;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Interfaces\IFileFormat;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Records\PageRecord;
use kalanis\kw_mapper\Storage\Shared\FormatFiles;
use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\Storage;
use kalanis\kw_storage\Storage\Key\StaticPrefixKey;
use kalanis\kw_storage\StorageException;
use Traversable;


class FileTest extends CommonTestClass
{
    public function setUp(): void
    {
        StaticPrefixKey::setPrefix(realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data') . DIRECTORY_SEPARATOR);
    }

    public function tearDown(): void
    {
        $ld = new StaticPrefixKey();
        $path = $ld->fromSharedKey('') . $this->getTestFile1();
        if (is_file($path)) {
            @unlink($path);
        }
        StaticPrefixKey::setPrefix('');
    }

    public function testContentOk(): void
    {
        $path = $this->getTestFile1();
        $lib = new Mappers\Storage\PageContent();
        $lib->setSource($path);
        $this->assertEquals($path, $lib->getAlias());
    }

    /**
     * @throws MapperException
     */
    public function testCannotLoad(): void
    {
        $rec = new PageRecord();
        $rec->path = $this->getTestFile1();
        $rec->content = 'okmijnuhbzgvtfcrdxesy';

        $lib = new XFailContentStorage();
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Unable to read from source');
        $lib->load($rec);
    }

    /**
     * @throws MapperException
     */
    public function testCannotSave(): void
    {
        $rec = new PageRecord();
        $rec->path = $this->getTestFile1();
        $rec->content = 'okmijnuhbzgvtfcrdxesy';

        $lib = new XFailContentStorage();
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Unable to write into source');
        $lib->save($rec);
    }

    /**
     * @throws MapperException
     */
    public function testCannotDelete(): void
    {
        $rec = new PageRecord();
        $rec->path = $this->getTestFile1();
        $rec->content = 'okmijnuhbzgvtfcrdxesy';

        $lib = new XFailContentStorage();
        $this->assertFalse($lib->delete($rec));
        $rec->path = 'cannot_be_found';
        $this->assertTrue($lib->delete($rec));
    }

    /**
     * @throws MapperException
     */
    public function testCannotSearch(): void
    {
        $rec = new KeyValueRecord();
        $rec->key = $this->getTestFile1();
        $rec->content = 'okmijnuhbzgvtfcrdxesy';

        $lib = new XFailStorageKeyValue();
        $result = $lib->loadMultiple($rec);
        $this->assertEmpty($result);
        $this->assertEquals([], $result);
    }

    /**
     * @throws MapperException
     */
    public function testSearchDir(): void
    {
        $rec = new KeyValueRecord();
        $rec->key = $this->getTestDir();
        $rec->content = '';

        $lib = new Mappers\Storage\KeyValue();
        $result = $lib->loadMultiple($rec);
        $this->assertNotEmpty($result);
    }

    /**
     * @throws MapperException
     */
    public function testCannotLoadData1(): void
    {
        $rec = new XPageContent();
        $rec->key = $this->getTestFile2();

        $lib = new XPageContentSoloMapper();
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Cannot load data array from storage');
        $lib->load($rec);
    }

    /**
     * @throws MapperException
     */
    public function testCannotLoadData2(): void
    {
        $rec = new XPageContent();
        $rec->key = $this->getTestFile2();

        $lib = new XPageContentEmptyMapper();
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Cannot load data entry from storage');
        $lib->load($rec);
    }

    protected function getTestFile1(): string
    {
        return 'fileTest.txt';
    }

    protected function getTestFile2(): string
    {
        return 'target' . DIRECTORY_SEPARATOR . 'other2.htm';
    }

    protected function getTestDir(): string
    {
        return 'target' . DIRECTORY_SEPARATOR;
    }
}


class XFailContentStorage extends Mappers\Storage\PageContent
{
    public function getStorage($storageParams = null): IStorage
    {
        return new XFailStorage(
            new Storage\Key\DefaultKey(),
            new Storage\Target\Volume()
        );
    }
}


class XFailStorageKeyValue extends Mappers\Storage\KeyValue
{
    public function getStorage($storageParams = null): IStorage
    {
        return new XFailStorage(
            new Storage\Key\DefaultKey(),
            new Storage\Target\Volume()
        );
    }
}


class XFailStorage extends Storage\Storage
{
    public function read(string $sharedKey)
    {
        throw new StorageException('XFail mock fail read');
    }

    public function write(string $sharedKey, $data, ?int $timeout = null): bool
    {
        throw new StorageException('XFail mock fail write');
    }

    public function remove(string $sharedKey): bool
    {
        throw new StorageException('XFail mock fail write');
    }

    public function lookup(string $mask): Traversable
    {
        throw new StorageException('XFail mock fail lookup');
    }

    public function exists(string $sharedKey): bool
    {
        return ('cannot_be_found' != $sharedKey);
    }
}


/**
 * Class KeyValueRecord
 * @package MappersTests\Storage
 * @property string $key
 * @property string $content
 */
class KeyValueRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('key', IEntryType::TYPE_STRING, 512);
        $this->addEntry('content', IEntryType::TYPE_STRING, PHP_INT_MAX);
        $this->setMapper(KeyValueMapper::class);
    }
}


class KeyValueMapper extends Mappers\Storage\PageContent
{
    protected function setMap(): void
    {
        $this->setStorage();
        $this->setPathKey('key');
        $this->setContentKey('content');
        $this->setFormat(FormatFiles\SinglePage::class);
    }
}


class SoloArrayFormat implements IFileFormat
{
    public function unpack(string $content): array
    {
        return (array) $content;
    }

    public function pack(array $records): string
    {
        return strval(reset($records));
    }
}


class EmptyArrayFormat implements IFileFormat
{
    public function unpack(string $content): array
    {
        return [[]];
    }

    public function pack(array $records): string
    {
        return '';
    }
}


class XPageContentSoloMapper extends Mappers\Storage\PageContent
{
    protected function setMap(): void
    {
        $this->setPathKey('key');
        $this->setContentKey('content');
        $this->setFormat(SoloArrayFormat::class);
    }
}


class XPageContentEmptyMapper extends Mappers\Storage\PageContent
{
    protected function setMap(): void
    {
        $this->setPathKey('key');
        $this->setContentKey('content');
        $this->setFormat(EmptyArrayFormat::class);
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
        $this->setMapper(XPageContentSoloMapper::class);
    }
}

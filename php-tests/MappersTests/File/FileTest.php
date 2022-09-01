<?php

namespace MappersTests\File;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Records\PageRecord;
use kalanis\kw_storage\Storage;
use kalanis\kw_storage\StorageException;
use Traversable;


class FileTest extends CommonTestClass
{
    public function tearDown(): void
    {
        $path = $this->getTestFile();
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public function testContentOk(): void
    {
        $path = $this->getTestFile();
        $lib = new Mappers\File\PageContent();
        $lib->setSource($path);
        $this->assertEquals($path, $lib->getAlias());
    }

    /**
     * @throws MapperException
     */
    public function testCannotLoad(): void
    {
        $rec = new PageRecord();
        $rec->path = $this->getTestFile();
        $rec->content = 'okmijnuhbzgvtfcrdxesy';

        $lib = new XFailContent();
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
        $rec->path = $this->getTestFile();
        $rec->content = 'okmijnuhbzgvtfcrdxesy';

        $lib = new XFailContent();
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
        $rec->path = $this->getTestFile();
        $rec->content = 'okmijnuhbzgvtfcrdxesy';

        $lib = new XFailContent();
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
        $rec->key = $this->getTestFile();
        $rec->content = 'okmijnuhbzgvtfcrdxesy';

        $lib = new XFailKeyValue();
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

        $lib = new Mappers\File\KeyValue();
        $result = $lib->loadMultiple($rec);
        $this->assertNotEmpty($result);
    }

    protected function getTestFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'fileTest.txt';
    }

    protected function getTestDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'target' . DIRECTORY_SEPARATOR;
    }
}


class XFailContent extends Mappers\File\PageContent
{
    public function getStorage(): Storage\Storage
    {
        return new XFailStorage(
            new Storage\Key\DefaultKey(),
            new Storage\Target\Volume()
        );
    }
}


class XFailKeyValue extends Mappers\File\KeyValue
{
    public function getStorage(): Storage\Storage
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
 * @package MappersTests\File
 * @property string key
 * @property string content
 */
class KeyValueRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('key', IEntryType::TYPE_STRING, 512);
        $this->addEntry('content', IEntryType::TYPE_STRING, PHP_INT_MAX);
        $this->setMapper('\MappersTests\File\KeyValueMapper');
    }
}


class KeyValueMapper extends Mappers\File\PageContent
{
    protected function setMap(): void
    {
        $this->setPathKey('key');
        $this->setContentKey('content');
        $this->setFormat('\kalanis\kw_mapper\Storage\File\Formats\SinglePage');
    }
}

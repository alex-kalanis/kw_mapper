<?php

namespace StorageTests\Files;


use CommonTestClass;
use kalanis\kw_files\Interfaces\IProcessFiles;
use kalanis\kw_files\Processing\Volume\ProcessFile;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Storage\PageContent;
use kalanis\kw_mapper\Records\PageRecord;
use kalanis\kw_mapper\Storage;
use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\Storage\Key\StaticPrefixKey;
use kalanis\kw_storage\Storage\Storage as Store;
use kalanis\kw_storage\StorageException;


class TraitTest extends CommonTestClass
{
    public function setUp(): void
    {
        StaticPrefixKey::setPrefix(realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'target'));
    }

    public function tearDown(): void
    {
        StaticPrefixKey::setPrefix('');
    }

    /**
     * @throws MapperException
     */
    public function testStoragePkFail(): void
    {
        $data = new StorageStoragePage();
        $data->noPks();
        $this->expectException(MapperException::class);
        $data->load(new PageRecord());
    }

    /**
     * @throws MapperException
     * @throws StorageException
     */
    public function testStorageInstances(): void
    {
        // set once, propagate everywhere
        $data1 = new CStorage();
        $data2 = new StorageStoragePage();
        $this->assertInstanceOf(Store::class, $data1->getStore());
        $this->assertInstanceOf(Store::class, $data2->getStore());
        $this->assertEquals($data1->getStore(), $data2->getStore());
    }

    /**
     * @throws MapperException
     */
    public function testStorageInstanceFail(): void
    {
        $data = new CStorage();
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Storage cannot be empty!');
        $data->getEmptyStore();
    }

    /**
     * @throws MapperException
     * @throws StorageException
     */
    public function testFileInstances(): void
    {
        // set once, propagate everywhere
        $data1 = new CFile();
        $data1->setAccessor(new ProcessFile());
        $data2 = new StorageStoragePage();
        $this->assertInstanceOf(IProcessFiles::class, $data1->getStore());
        $this->assertNotEquals($data1->getStore(), $data2->getStore());
    }

    public function testFilePaths(): void
    {
        $data1 = new CFile();
        $this->assertEmpty($data1->getPt());
        $pt = ['uhb', 'efvb', 'gsr', 'aht'];
        $data1->setPt($pt);
        $this->assertEquals($pt, $data1->getPt());
    }

    /**
     * @throws MapperException
     */
    public function testFileInstanceFails(): void
    {
        $data1 = new CFile();
        $data1->setAccessor(null);
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('You must set the files accessor - instance of *IProcessFiles* - first!');
        $data1->getStore();
    }

    public function testNewLines(): void
    {
        $content = 'adsfghjk' . "\r" . 'yxcvbnml' . "\n" . 'qwertzui' . "\r\n" . 'op';
        $data = new Nl();
        $this->assertEquals($content, $data->unescapeNl($data->escapeNl($content)));
    }

    public function testFormatData(): void
    {
        $data = new XFormat();
        $this->assertEmpty($data->getFormat());
        $data->setFormat('qaywsxedc');
        $this->assertEquals('qaywsxedc', $data->getFormat());
    }
}


class Nl
{
    use Storage\Shared\FormatFiles\TNl;
}


class CStorage
{
    use Storage\Storage\TStorage;

    /**
     * @throws MapperException
     * @return IStorage
     */
    public function getStore(): IStorage
    {
        $this->clearStorage();
        return $this->getStorage();
    }

    /**
     * @throws MapperException
     * @return IStorage
     */
    public function getEmptyStore(): IStorage
    {
        $this->setStorage(null);
        return $this->getStorage();
    }
}


class CFile
{
    use Storage\File\TFile;

    public function setAccessor(?IProcessFiles $file): void
    {
        $this->setFileAccessor($file);
    }

    /**
     * @throws MapperException
     * @return IProcessFiles
     */
    public function getStore(): IProcessFiles
    {
        return $this->getFileAccessor();
    }

    /**
     * @param string[] $pt
     */
    public function setPt(array $pt): void
    {
        $this->setPath($pt);
    }

    /**
     * @return string[]
     */
    public function getPt(): array
    {
        return $this->getPath();
    }
}


class XFormat
{
    use Storage\Shared\TFormat;
}


class StorageStoragePage extends PageContent
{
    /**
     * @throws StorageException
     * @return IStorage
     */
    public function getStore()
    {
        return $this->getStorage();
    }

    public function noPks(): void
    {
        $this->primaryKeys = [];
    }
}

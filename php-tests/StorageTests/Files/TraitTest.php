<?php

namespace StorageTests\Files;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\File\PageContent;
use kalanis\kw_mapper\Records\PageRecord;
use kalanis\kw_mapper\Storage\File;
use kalanis\kw_storage\Storage\Storage;


class TraitTest extends CommonTestClass
{
    public function testStoragePkFail(): void
    {
        $data = new StoragePage();
        $data->noPks();
        $this->expectException(MapperException::class);
        $data->load(new PageRecord());
    }

    public function testStorageInstances(): void
    {
        // set once, propagate everywhere
        $data1 = new CStorage();
        $data2 = new StoragePage();
        $this->assertInstanceOf(Storage::class, $data1->getStore());
        $this->assertInstanceOf(Storage::class, $data2->getStore());
        $this->assertEquals($data1->getStore(), $data2->getStore());
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
    use File\Formats\TNl;
}


class CStorage
{
    use File\TStorage;

    public function getStore()
    {
        return $this->getStorage();
    }
}


class XFormat
{
    use File\TFormat;
}


class StoragePage extends PageContent
{
    public function getStore()
    {
        return $this->getStorage();
    }

    public function noPks(): void
    {
        $this->primaryKeys = [];
    }
}

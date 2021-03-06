<?php

namespace StorageTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\INl;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\File;
use kalanis\kw_mapper\Records\PageRecord;
use kalanis\kw_mapper\Storage;


class TraitTest extends CommonTestClass
{
    public function testStoragePkFail()
    {
        $data = new StoragePage();
        $data->noPks();
        $this->expectException(MapperException::class);
        $data->load(new PageRecord());
    }

    public function testStorageInstances()
    {
        // set once, propagate everywhere
        $data1 = new CStorage();
        $data2 = new StoragePage();
        $this->assertInstanceOf('\kalanis\kw_storage\Storage\Storage', $data1->getStore());
        $this->assertInstanceOf('\kalanis\kw_storage\Storage\Storage', $data2->getStore());
        $this->assertEquals($data1->getStore(), $data2->getStore());
    }

    public function testNewLines()
    {
        $content = implode(INl::NL_REPLACEMENT, ['adsfghjk', 'yxcvbnml', 'qwertzui', 'op']);
        $data = new Nl();
        $this->assertEquals($content, $data->nlToStr($data->strToNl($content)));
    }
}


class Nl
{
    use Storage\File\Formats\TNl;
}


class CStorage
{
    use Storage\File\TStorage;

    public function getStore()
    {
        return $this->getStorage();
    }
}


class StoragePage extends File\PageContent
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

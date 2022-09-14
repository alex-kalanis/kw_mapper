<?php

namespace StorageTests;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage;


class ExtTest extends CommonTestClass
{
    public function testExtNotExist(): void
    {
        $data = new XCheckExt();
        $this->expectException(MapperException::class);
        $data->checkExtension('Unknown_ONE');
    }

    public function testExtExist(): void
    {
        $data = new XCheckExt();
        $data->checkExtension('pdo');
        $this->assertEmpty(false);
    }
}


class XCheckExt
{
    use Storage\Shared\TCheckExt;
}

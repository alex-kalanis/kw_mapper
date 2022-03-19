<?php

namespace StorageTests\Files;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\File\MultiContent\Multiton;
use kalanis\kw_mapper\Storage\File\Formats;


class MultiContentTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testStoredFails(): void
    {
        $content = Multiton::getInstance();
        $this->expectException(MapperException::class);
        $content->getContent('undefined');
    }

    /**
     * @throws MapperException
     */
    public function testStoredPass(): void
    {
        $factory = Formats\Factory::getInstance();
        $content = Multiton::getInstance();
        $content->init('foo', $factory->getFormatClass('\kalanis\kw_mapper\Storage\File\Formats\SinglePage'));
        $content->setContent('foo', ['foo-bar-baz-anf-bvt-xcu-xdh']);
        $this->assertEquals(['foo-bar-baz-anf-bvt-xcu-xdh'], $content->getContent('foo'));
        $this->assertInstanceOf('\kalanis\kw_mapper\Interfaces\IFileFormat', $content->getFormatClass('foo'));
    }
}

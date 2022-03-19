<?php

namespace StorageTests\Files;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\File\ContentMultiton;
use kalanis\kw_mapper\Storage\File\Formats;


class FilesTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testStoredFails(): void
    {
        $content = ContentMultiton::getInstance();
        $this->expectException(MapperException::class);
        $content->getContent('undefined');
    }

    /**
     * @throws MapperException
     */
    public function testStoredPass(): void
    {
        $factory = Formats\Factory::getInstance();
        $content = ContentMultiton::getInstance();
        $content->init('foo', $factory->getFormatClass('\kalanis\kw_mapper\Storage\File\Formats\SinglePage'));
        $content->setContent('foo', ['foo-bar-baz-anf-bvt-xcu-xdh']);
        $this->assertEquals(['foo-bar-baz-anf-bvt-xcu-xdh'], $content->getContent('foo'));
        $this->assertInstanceOf('\kalanis\kw_mapper\Interfaces\IFileFormat', $content->getFormatClass('foo'));
    }
}

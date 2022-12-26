<?php

namespace StorageTests\Files;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Shared\FormatFiles;
use kalanis\kw_mapper\Storage\Storage\MultiContent\Multiton;


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
        $factory = FormatFiles\Factory::getInstance();
        $content = Multiton::getInstance();
        $content->init('foo', $factory->getFormatClass(FormatFiles\SinglePage::class));
        $content->setContent('foo', ['foo-bar-baz-anf-bvt-xcu-xdh']);
        $this->assertEquals(['foo-bar-baz-anf-bvt-xcu-xdh'], $content->getContent('foo'));
        $this->assertInstanceOf(Interfaces\IFileFormat::class, $content->getFormatClass('foo'));
    }
}

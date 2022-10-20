<?php

namespace StorageTests\Files;


use CommonTestClass;
use kalanis\kw_mapper\Adapters;
use kalanis\kw_mapper\Interfaces;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\File\Formats;


class FileFormatsTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testFactoryNoClass(): void
    {
        $factory = Formats\Factory::getInstance();
        $this->expectException(MapperException::class);
        $factory->getFormatClass('undefined');
    }

    /**
     * @throws MapperException
     */
    public function testFactoryWrongClass(): void
    {
        $factory = Formats\Factory::getInstance();
        $this->expectException(MapperException::class);
        $factory->getFormatClass(Adapters\MappedStdClass::class);
    }

    /**
     * @throws MapperException
     */
    public function testFactoryRun(): void
    {
        $factory = Formats\Factory::getInstance();
        $class = $factory->getFormatClass(Formats\SinglePage::class);
        $this->assertInstanceOf(Interfaces\IFileFormat::class, $class);
        $this->assertEquals($class, $factory->getFormatClass(Formats\SinglePage::class));
    }

    /**
     * @throws MapperException
     */
    public function testSinglePage(): void
    {
        $data = ['foo-bar-baz-anf-bvt-xcu-xdh'];
        $format = new Formats\SinglePage();
        $this->assertEquals($data, $format->unpack($format->pack($data)));
    }

    /**
     * @throws MapperException
     */
    public function testSeparated(): void
    {
        $data = $this->typesToProvider();
        $format = new Formats\SeparatedElements();
        $format->setDelimiters('#');
        $this->assertEquals([
            ['0', '1', '', ''],
            ['1', '1', '1', ''],
            ['10', '4', '', ''],
            ['15', '2', '15', ''],
            ['18.8', '3', '18.8', ''],
            ['lkjhgdf', '4', 'lkjhgdf', ''],
        ], $format->unpack($format->pack($data). PHP_EOL . PHP_EOL));
    }

    /**
     * @throws MapperException
     * @requires extension yaml
     * @requires function yaml_parse
     * @requires function yaml_emit
     * -- markTestSkipped()
     */
    public function testYaml(): void
    {
        $data = $this->typesToProvider();
        $format = new Formats\Yaml();
        $this->assertEquals($data, $format->unpack($format->pack($data)));
        $this->expectException(MapperException::class);
        $format->unpack("\e\tasdfghjkl\t\e\r\n\r\nasdfgjkl");
    }

    /**
     * @throws MapperException
     * @requires function parse_ini_string
     * -- markTestSkipped()
     */
    public function testIni(): void
    {
        $data = $this->typesToProvider();
        $format = new Formats\Ini();
        $this->assertEquals($data, $format->unpack($format->pack($data)));
        $this->expectException(MapperException::class);
        $format->unpack("\e\tasdfghjkl\t\e\r\n\r\nasdfgjkl?{}|&~![()^");
    }

    /**
     * @throws MapperException
     */
    public function testCsv(): void
    {
        $data = $this->typesToProvider();
        $format = new Formats\Csv();
        $format->setDelimiters(PHP_EOL);
        $this->assertEquals([
            ['0', '1', '', ''],
            ['1', '1', '1', ''],
            ['10', '4', '', ''],
            ['15', '2', '15', ''],
            ['18.8', '3', '18.8', ''],
            ['lkjhgdf', '4', 'lkjhgdf', ''],
        ], $format->unpack($format->pack($data)));
    }

    public function typesToProvider(): array
    {
        return [
            'obj1' => ['a' => '0', 'b' => Interfaces\IEntryType::TYPE_BOOLEAN, 'c' => false],
            'obj2' => ['a' => '1', 'b' => Interfaces\IEntryType::TYPE_BOOLEAN, 'c' => true],
            'obj3' => ['a' => '10', 'b' => Interfaces\IEntryType::TYPE_STRING, 'c' => null],
            'obj4' => ['a' => '15', 'b' => Interfaces\IEntryType::TYPE_INTEGER, 'c' => 15],
            'obj5' => ['a' => '18.8', 'b' => Interfaces\IEntryType::TYPE_FLOAT, 'c' => 18.8],
            'obj6' => ['a' => 'lkjhgdf', 'b' => Interfaces\IEntryType::TYPE_STRING, 'c' => 'lkjhgdf'],
        ];
    }
}

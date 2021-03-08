<?php

namespace MappersTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\File;
use kalanis\kw_mapper\Storage;


class TraitTest extends CommonTestClass
{
    public function testContentOk()
    {
        $data = new Content();
        $data->setContentKey('lkjhgfd');
        $this->assertEquals('lkjhgfd', $data->getKey());
    }

    public function testContentFail()
    {
        $data = new Content();
        $this->expectException(MapperException::class);
        $data->getKey();
    }

    /**
     * @param mixed $want
     * @param int $type
     * @param mixed $input
     * @dataProvider typesFromProvider
     */
    public function testTypesFrom($want, int $type, $input)
    {
        $data = new Translate();
        $this->assertEquals($want, $data->from($type, $input));
    }

    public function typesFromProvider(): array
    {
        return [
            [false, IEntryType::TYPE_BOOLEAN, 0],
            [false, IEntryType::TYPE_BOOLEAN, ''],
            [true, IEntryType::TYPE_BOOLEAN, 7],
            [true, IEntryType::TYPE_BOOLEAN, '3'],
            [15, IEntryType::TYPE_INTEGER, 15.3],
            [4358, IEntryType::TYPE_INTEGER, '4358'],
            [18.8, IEntryType::TYPE_FLOAT, '18.8'],
            [18.8, IEntryType::TYPE_FLOAT, '18.8'],
            [['foo', 'bar'], IEntryType::TYPE_ARRAY, 'a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}'],
            ['lkjhgdf', IEntryType::TYPE_STRING, 'lkjhgdf'],
        ];
    }

    /**
     * @param $want
     * @param int $type
     * @param $input
     * @dataProvider typesToProvider
     */
    public function testTypesTo($want, int $type, $input)
    {
        $data = new Translate();
        $this->assertEquals($want, $data->to($type, $input));
    }

    public function typesToProvider(): array
    {
        return [
            ['0', IEntryType::TYPE_BOOLEAN, false],
            ['1', IEntryType::TYPE_BOOLEAN, true],
            ['15', IEntryType::TYPE_INTEGER, 15],
            ['18.8', IEntryType::TYPE_FLOAT, 18.8],
            ['a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}', IEntryType::TYPE_ARRAY, ['foo', 'bar']],
            ['lkjhgdf', IEntryType::TYPE_STRING, 'lkjhgdf'],
        ];
    }
}


class Content
{
    use File\TContent;

    public function getKey(): string
    {
        return $this->getContentKey();
    }
}


class Translate
{
    use File\TTranslate;

    public function from(int $type, $content)
    {
        return $this->translateTypeFrom($type, $content);
    }

    public function to(int $type, $content)
    {
        return $this->translateTypeTo($type, $content);
    }
}

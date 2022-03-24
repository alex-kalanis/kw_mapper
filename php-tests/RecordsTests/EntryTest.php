<?php

namespace RecordsTests;


use CommonTestClass;
use kalanis\kw_mapper\Adapters\MappedStdClass;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Records\Entry;
use kalanis\kw_mapper\Records\TFill;


class EntryTest extends CommonTestClass
{
    public function testSimple(): void
    {
        $data = Entry::getInstance();
        $this->assertEmpty($data->getType());
        $this->assertEmpty($data->getParams());
        $this->assertEmpty($data->getData());
        $this->assertFalse($data->isFromStorage());

        $data->setData('different %s %s', true);
        $this->assertEquals('different %s %s', $data->getData());
        $this->assertTrue($data->isFromStorage());

        $data->setParams('conv');
        $this->assertEquals('conv', $data->getParams());

        $data->setType(9999);
        $this->assertEquals(9999, $data->getType());

        $data2 = clone $data;
        $data2->setData('new test', false);
        $this->assertEquals('new test', $data2->getData());
        $this->assertFalse($data2->isFromStorage());
        $this->assertNotEquals('new test', $data->getData());
        $this->assertEquals('different %s %s', $data->getData());
        $this->assertTrue($data->isFromStorage());
    }

    /**
     * @param int $type
     * @param mixed $value
     * @dataProvider fillsProvider
     */
    public function testFillTypes(int $type, $value): void
    {
        $fill = new Fill();
        $data = Entry::getInstance();
        $data->setType($type);
        $fill->xFill($data, $value);
        $this->assertEquals($value, $data->getData());
    }

    public function fillsProvider(): array
    {
        return [
            [IEntryType::TYPE_BOOLEAN, false],
            [IEntryType::TYPE_BOOLEAN, true],
            [IEntryType::TYPE_INTEGER, 15],
            [IEntryType::TYPE_FLOAT, 18.8],
            [IEntryType::TYPE_ARRAY, ['foo', 'bar']],
            [IEntryType::TYPE_STRING, 'lkjhgdf'],
            [IEntryType::TYPE_OBJECT, new MappedStdClass()],
        ];
    }
}


class Fill
{
    use TFill;

    public function xFill(Entry &$entry, $value)
    {
        $this->typedFill($entry, $value);
    }
}

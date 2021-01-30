<?php

namespace RecordsTests;


use CommonTestClass;
use kalanis\kw_mapper\Records\Entry;


class EntryTest extends CommonTestClass
{
    public function testSimple()
    {
        $data = Entry::getInstance();
        $this->assertEmpty($data->getType());
        $this->assertEmpty($data->getParams());
        $this->assertEmpty($data->getData());
        $this->assertFalse($data->isChanged());

        $data->setData('different %s %s');
        $this->assertEquals('different %s %s', $data->getData());
        $this->assertTrue($data->isChanged());

        $data->setParams('conv');
        $this->assertEquals('conv', $data->getParams());

        $data->setType(9999);
        $this->assertEquals(9999, $data->getType());

        $data2 = clone $data;
        $data2->setData('new test', false);
        $this->assertEquals('new test', $data2->getData());
        $this->assertFalse($data2->isChanged());
        $this->assertNotEquals('new test', $data->getData());
        $this->assertEquals('different %s %s', $data->getData());
        $this->assertTrue($data->isChanged());
    }
}

<?php

namespace DebugsTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records\ASimpleRecord;


class ContentTableTest extends CommonTestClass
{
    public function testSimple()
    {
        $data = new ShortMessage();

        $this->assertEmpty($data->id);
        $this->assertEmpty($data->date);

        $data->id = 24;
        $data->load();
        $this->assertEquals(1383158961, $data->date);
    }

    public function testLoadMultiple()
    {
        $data = new ShortMessage();
        $data->title = 'Karuma z Gilanu';
        $listing = $data->loadMultiple();
        $this->assertEquals(6, count($listing));
    }
}


/**
 * Class ShortMessage
 * @package RecordsTests
 * @property int id
 * @property int date
 * @property string title
 * @property string content
 */
class ShortMessage extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 4096);
        $this->addEntry('date', IEntryType::TYPE_INTEGER, PHP_INT_MAX);
        $this->addEntry('title', IEntryType::TYPE_STRING, 1024);
        $this->addEntry('content', IEntryType::TYPE_STRING, 8192);
        $this->setMapper('\RecordsTests\ShortMessageMapper');
    }
}


class ShortMessageMapper extends Mappers\File\ATable
{
    protected function setMap(): void
    {
        $this->setFile(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' .  DIRECTORY_SEPARATOR . 'index.short');
        $this->setFormat('\kalanis\kw_mapper\Storage\File\Formats\SeparatedElements');
        $this->orderFromFirst(false);
        $this->setRelation('id', 0);
        $this->setRelation('date', 1);
        $this->setRelation('title', 2);
        $this->setRelation('content', 3);
        $this->addPrimaryKey('id');
    }
}

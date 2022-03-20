<?php

use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Mappers\File\ATable;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Storage\Database;
use kalanis\kw_mapper\Storage\Shared;
use PHPUnit\Framework\TestCase;


/**
 * Class CommonTestClass
 * The structure for mocking and configuration seems so complicated, but it's necessary to let it be totally idiot-proof
 */
class CommonTestClass extends TestCase
{
}


class Builder extends Shared\QueryBuilder
{
    public function resetCounter(): void
    {
        static::$uniqId = 0;
    }
}


class Builder2 extends Database\QueryBuilder
{
    public function resetCounter(): void
    {
        static::$uniqId = 0;
    }
}


class StrObjMock
{
    public function __toString()
    {
        return 'strObj';
    }
}


/**
 * Class TableRecord
 * Source file dumped from kw_menu
 * @property string file
 * @property int order
 * @property string title
 * @property string desc
 * @property bool sub
 */
class TableRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('file', IEntryType::TYPE_STRING, 512);
        $this->addEntry('order', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('title', IEntryType::TYPE_STRING, 512);
        $this->addEntry('desc', IEntryType::TYPE_STRING, 512);
        $this->addEntry('sub', IEntryType::TYPE_BOOLEAN);
        $this->setMapper('\TableMapper');
    }
}


class TableMapper extends ATable
{
    protected function setMap(): void
    {
        $this->setFormat('\kalanis\kw_mapper\Storage\File\Formats\SeparatedElements');
        $this->setSource(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'target.meta');
        $this->setRelation('file', 0);
        $this->setRelation('order', 1);
        $this->setRelation('title', 2);
        $this->setRelation('desc', 3);
        $this->setRelation('sub', 4);
        $this->addPrimaryKey('file');
    }
}

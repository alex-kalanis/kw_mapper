<?php

namespace SearchTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Database\ADatabase;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Search\Connector\Database\TRecords;
use kalanis\kw_mapper\Storage;


class RecordsTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testSimpleLookup(): void
    {
        $record = new XTRecords();
        $record->initRecordLookup(new XaRecordChild());
        $this->assertEquals(1, count($record->getRecords()));
        $child = $record->recordLookup('prt');
        $this->assertEquals(2, count($record->getRecords()));
        $this->assertInstanceOf('\SearchTests\XaRecordParent', $child->getRecord());
        $this->assertEquals($child, $record->recordLookup('prt'));
        $this->assertEmpty($record->recordLookup('unknown'));
    }
}


class XTRecords
{
    use TRecords;
}


/**
 * Class XaRecordParent
 * @package SearchTests
 * @property int $id
 * @property string $name
 * @property XaRecordChild[] $chld
 */
class XaRecordParent extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 512);
        $this->addEntry('name', IEntryType::TYPE_STRING, 512);
        $this->addEntry('chld', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper('\SearchTests\XaMapperParent');
    }
}


/**
 * Class XaRecordChild
 * @package SearchTests
 * @property int $id
 * @property string $name
 * @property int $prtId
 * @property XaRecordParent[] $prt
 */
class XaRecordChild extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 512);
        $this->addEntry('name', IEntryType::TYPE_STRING, 512);
        $this->addEntry('prtId', IEntryType::TYPE_INTEGER, 64); // ID of remote
        $this->addEntry('prt', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper('\SearchTests\XaMapperChild');
    }
}


class XaMapperParent extends ADatabase
{
    public function setMap(): void
    {
        $this->setSource('testing');
        $this->setTable('kw_mapper_parent_testing');
        $this->setRelation('id', 'kmpt_id');
        $this->setRelation('name', 'kmpt_name');
        $this->addPrimaryKey('id');
        $this->addForeignKey('chld', '\SearchTests\XaRecordChild', 'chldId', 'id');
    }
}


class XaMapperChild extends ADatabase
{
    public function setMap(): void
    {
        $this->setSource('testing');
        $this->setTable('kw_mapper_child_testing');
        $this->setRelation('id', 'kmct_id');
        $this->setRelation('name', 'kmct_name');
        $this->setRelation('prtId', 'kmpt_id');
        $this->addPrimaryKey('id');
        $this->addForeignKey('prt', '\SearchTests\XaRecordParent', 'prtId', 'id');
    }
}

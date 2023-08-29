<?php

namespace SearchTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Database\ADatabase;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Search\Connector\Database\RecordsInJoin;
use kalanis\kw_mapper\Search\Connector\Database\TRecordsInJoins;
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
        $this->assertEquals(1, count($record->getRecordsInJoin()));
        $child = $record->recordLookup('prt');
        $this->assertEquals(2, count($record->getRecordsInJoin()));
        $this->assertInstanceOf(XaRecordParent::class, $child->getRecord());
        $this->assertEquals($child, $record->recordLookup('prt'));
        $this->assertEmpty($record->recordLookup('unknown'));
    }

    /**
     * @throws MapperException
     */
    public function testFailedLookupClassNotExists(): void
    {
        $record = new XTRecords();
        $record->initRecordLookup(new XaRecordChild());
        $record->addRecord(new XbRecordParent(), 'fail');
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Class this_class_does_not_exists does not exist');
        $record->recordLookup('fail');
    }
}


class XTRecords
{
    use TRecordsInJoins;

    /**
     * @param ARecord $record
     * @param string|null $alias
     * @throws MapperException
     */
    public function addRecord(ARecord $record, ?string $alias = null): void
    {
        $rec = new RecordsInJoin();
        $rec->setData(
            $record,
            $record->getMapper()->getAlias(),
            $alias,
            ''
        );
        $this->recordsInJoin[$rec->getStoreKey()] = $rec;
    }
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
        $this->setMapper(XaMapperParent::class);
    }
}


/**
 * Class XbRecordParent
 * @package SearchTests
 * @property int $id
 * @property string $name
 * @property XaRecordChild[] $chld
 */
class XbRecordParent extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 512);
        $this->addEntry('name', IEntryType::TYPE_STRING, 512);
        $this->addEntry('chld', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->addEntry('fail', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time; but this one is intentionally failed
        $this->setMapper(XbMapperParent::class);
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
        $this->setMapper(XaMapperChild::class);
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
        $this->addForeignKey('chld', XaRecordChild::class, 'chldId', 'id');
    }
}


class XbMapperParent extends ADatabase
{
    public function setMap(): void
    {
        $this->setSource('testing');
        $this->setTable('kw_mapper_parent_testing_2');
        $this->setRelation('id', 'kmpt2_id');
        $this->setRelation('name', 'kmpt2_name');
        $this->addPrimaryKey('id');
        $this->addForeignKey('chld', XaRecordChild::class, 'chldId', 'id');
        $this->addForeignKey('fail', 'this_class_does_not_exists', 'chldId', 'id');
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
        $this->addForeignKey('prt', XaRecordParent::class, 'prtId', 'id');
    }
}

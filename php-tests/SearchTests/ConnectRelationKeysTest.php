<?php

namespace SearchTests;


use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Database\ADatabase;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Search\Search;
use kalanis\kw_mapper\Storage;


/**
 * Class ConnectRelationKeysTest
 * @package SearchTests
 * @requires extension PDO
 * @requires extension pdo_sqlite
 */
class ConnectRelationKeysTest extends AConnectTests
{
    /**
     * @throws MapperException
     */
    public function testFailedDifferentChildKey(): void
    {
        $search = new Search(new XConnectRecordBadRelationChild());
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Unknown relation key *parentId* in mapper for parent *kw_mapper_child_testing*');
        $search->child('parents', IQueryBuilder::JOIN_BASIC);
    }

    /**
     * @throws MapperException
     */
    public function testFailedUnknownChildKey(): void
    {
        $search = new Search(new XConnectRecordBadEntryChild());
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Unknown relation key *unknown* in mapper for child *prts*');
        $search->child('prts', IQueryBuilder::JOIN_BASIC);
    }
}


/**
 * Class XConnectRecordDifferentStorageChild
 * Different entry key to main parent than defined one
 * @package SearchTests
 * @property int $id
 * @property string $name
 * @property int $prtId
 * @property XConnectRecordParent[] $prts
 */
class XConnectRecordBadRelationChild extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 512);
        $this->addEntry('name', IEntryType::TYPE_STRING, 512);
        $this->addEntry('prtId', IEntryType::TYPE_INTEGER, 64); // ID of remote
        $this->addEntry('prts', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper(XConnectMapperBadRelationChild::class);
    }
}


class XConnectMapperBadRelationChild extends ADatabase
{
    public function setMap(): void
    {
        $this->setSource('test_sqlite_local_some');
        $this->setTable('kw_mapper_child_testing');
        $this->setRelation('id', 'kmct_id');
        $this->setRelation('name', 'kmct_name');
        $this->setRelation('prtId', 'kmpt_id');
        $this->addPrimaryKey('id');
        // local entry key alias is different from any available relation keys - cannot connect them together
        $this->addForeignKey('parents', XConnectRecordParent::class, 'parentId', 'id');
    }
}


/**
 * Class XConnectRecordBadEntryChild
 * @package SearchTests
 * @property int $id
 * @property string $name
 * @property int $prtId
 * @property XConnectRecordParent[] $prts
 */
class XConnectRecordBadEntryChild extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 512);
        $this->addEntry('name', IEntryType::TYPE_STRING, 512);
        $this->addEntry('prtId', IEntryType::TYPE_INTEGER, 64); // ID of remote
        $this->addEntry('prts', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper(XConnectMapperBadEntryChild::class);
    }
}


class XConnectMapperBadEntryChild extends ADatabase
{
    public function setMap(): void
    {
        $this->setSource('test_sqlite_local_some');
        $this->setTable('kw_mapper_child_testing');
        $this->setRelation('id', 'kmct_id');
        $this->setRelation('name', 'kmct_name');
        $this->setRelation('prtId', 'kmpt_id');
        $this->addPrimaryKey('id');
        // remote entry key is unknown in remote record - cannot connect them together
        $this->addForeignKey('prts', XConnectRecordParent::class, 'prtId', 'unknown');
    }
}

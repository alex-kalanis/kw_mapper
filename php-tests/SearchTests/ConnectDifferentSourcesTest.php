<?php

namespace SearchTests;


use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Database\ADatabase;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Search\Search;
use kalanis\kw_mapper\Storage;


class ConnectDifferentSourcesTest extends AConnectTests
{
    /**
     * @throws MapperException
     */
    public function testFailedDifferentSources(): void
    {
        $search = new Search(new XConnectRecordDifferentStorageChild());
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Parent *kw_mapper_child_testing* and child *prts* must both have the same source');
        $search->child('prts', IQueryBuilder::JOIN_BASIC);
    }
}


/**
 * Class XConnectRecordDifferentStorageChild
 * Different storage than main parent
 * @package SearchTests
 * @property int $id
 * @property string $name
 * @property int $prtId
 * @property XConnectRecordParent[] $prts
 */
class XConnectRecordDifferentStorageChild extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 512);
        $this->addEntry('name', IEntryType::TYPE_STRING, 512);
        $this->addEntry('prtId', IEntryType::TYPE_INTEGER, 64); // ID of remote
        $this->addEntry('prts', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper('\SearchTests\XConnectMapperDifferentStorageChild');
    }
}


class XConnectMapperDifferentStorageChild extends ADatabase
{
    public function setMap(): void
    {
        // this source is different from parent - cannot make a join
        $this->setSource('test_sqlite_local_else');
        $this->setTable('kw_mapper_child_testing');
        $this->setRelation('id', 'kmct_id');
        $this->setRelation('name', 'kmct_name');
        $this->setRelation('prtId', 'kmpt_id');
        $this->addPrimaryKey('id');
        $this->addForeignKey('prts', '\SearchTests\XConnectRecordParent', 'prtId', 'id');
    }
}

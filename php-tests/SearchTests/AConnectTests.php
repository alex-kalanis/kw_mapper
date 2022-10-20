<?php

namespace SearchTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Mappers\Database\ADatabase;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Storage;
use PDO;


abstract class AConnectTests extends CommonTestClass
{
    protected function setUp(): void
    {
        $conf1 = Storage\Database\Config::init()->setTarget(
            IDriverSources::TYPE_PDO_SQLITE,
            'test_sqlite_local_some',
            ':memory:',
            0,
            null,
            null,
            ''
        );
        $conf1->setParams(86000, true);
        Storage\Database\ConfigStorage::getInstance()->addConfig($conf1);

        $conf2 = Storage\Database\Config::init()->setTarget(
            IDriverSources::TYPE_PDO_SQLITE,
            'test_sqlite_local_else',
            ':memory:',
            0,
            null,
            null,
            ''
        );
        $conf2->setParams(86000, true);
        Storage\Database\ConfigStorage::getInstance()->addConfig($conf2);

        $database2 = Storage\Database\DatabaseSingleton::getInstance()->getDatabase($conf2);
        $database2->addAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $database1 = Storage\Database\DatabaseSingleton::getInstance()->getDatabase($conf1);
        $database1->addAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
}


/**
 * Class XConnectRecordParent
 * @package SearchTests
 * @property int $id
 * @property string $name
 * @property XConnectRecordGoodChild[] $chld
 */
class XConnectRecordParent extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 512);
        $this->addEntry('name', IEntryType::TYPE_STRING, 512);
        $this->addEntry('chld', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper(XConnectMapperParent::class);
    }
}


class XConnectMapperParent extends ADatabase
{
    public function setMap(): void
    {
        $this->setSource('test_sqlite_local_some');
        $this->setTable('kw_mapper_parent_testing');
        $this->setRelation('id', 'kmpt_id');
        $this->setRelation('name', 'kmpt_name');
        $this->addPrimaryKey('id');
        $this->addForeignKey('chld', XConnectRecordGoodChild::class, 'chldId', 'id');
    }
}


/**
 * Class XConnectRecordGoodChild
 * @package SearchTests
 * @property int $id
 * @property string $name
 * @property int $prtId
 * @property XConnectRecordParent[] $prts
 */
class XConnectRecordGoodChild extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 512);
        $this->addEntry('name', IEntryType::TYPE_STRING, 512);
        $this->addEntry('prtId', IEntryType::TYPE_INTEGER, 64); // ID of remote
        $this->addEntry('prts', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper(XConnectMapperGoodChild::class);
    }
}


class XConnectMapperGoodChild extends ADatabase
{
    public function setMap(): void
    {
        $this->setSource('test_sqlite_local_else');
        $this->setTable('kw_mapper_child_testing');
        $this->setRelation('id', 'kmct_id');
        $this->setRelation('name', 'kmct_name');
        $this->setRelation('prtId', 'kmpt_id');
        $this->addPrimaryKey('id');
        $this->addForeignKey('prts', XConnectRecordParent::class, 'prtId', 'id');
    }
}

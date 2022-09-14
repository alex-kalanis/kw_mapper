<?php

namespace StorageTests\Database;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Database\ADatabase;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Storage\Database\Config;
use kalanis\kw_mapper\Storage\Database\ConfigStorage;
use kalanis\kw_mapper\Storage\Database\DatabaseSingleton;
use kalanis\kw_mapper\Storage\Database\PDO\SQLite;
use PDO;


class DatabaseTest extends CommonTestClass
{
    /** @var null|SQLite */
    protected $database = null;

    /**
     * @throws MapperException
     */
    protected function setUp(): void
    {
        $conf = Config::init()->setTarget(
            IDriverSources::TYPE_PDO_SQLITE,
            'test_sqlite_local_test',
            ':memory:',
            0,
            null,
            null,
            ''
        );
        $conf->setParams(86000, true);
        ConfigStorage::getInstance()->addConfig($conf);
        $this->database = DatabaseSingleton::getInstance()->getDatabase($conf);
        $this->database->addAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * @throws MapperException
     */
    public function testUpdateBadPkFail(): void
    {
        $this->dataRefill();

        $rec = new SQLiteNameTestRecordBadPk();
        $rec->id = 15;
        $rec->name = 'out';
        $this->assertFalse($rec->getMapper()->xUpdateRecordByPk($rec));
    }

    /**
     * @throws MapperException
     */
    public function testUpdateNoPkFail(): void
    {
        $this->dataRefill();

        $rec = new SQLiteNameTestRecordNoPk();
        $rec->id = 25;
        $rec->name = 'out';
        $this->assertFalse($rec->getMapper()->xUpdateRecordByPk($rec));
    }

    /**
     * @throws MapperException
     */
    public function testLoadFail(): void
    {
        $this->dataRefill();

        $rec = new SQLiteNameTestRecord();
        $rec->id = 35;
        $this->assertFalse($rec->getMapper()->xLoadRecord($rec));
    }

    /**
     * @throws MapperException
     */
    public function testLoadBadPkFail(): void
    {
        $this->dataRefill();

        $rec = new SQLiteNameTestRecordBadPk();
        $rec->id = 45;
        $this->assertFalse($rec->getMapper()->xLoadRecordByPk($rec));
    }

    /**
     * @throws MapperException
     */
    public function testLoadNoPkFail(): void
    {
        $this->dataRefill();

        $rec = new SQLiteNameTestRecordNoPk();
        $rec->id = 55;
        $this->assertFalse($rec->getMapper()->xLoadRecordByPk($rec));
    }

    /**
     * @throws MapperException
     */
    public function testDeletePass(): void
    {
        $this->dataRefill();

        $rec = new SQLiteNameTestRecord();
        $rec->id = 65;
        $this->assertTrue($rec->getMapper()->xDeleteRecord($rec));
        $this->assertTrue($rec->getMapper()->xDeleteRecordByPk($rec));
    }

    /**
     * @throws MapperException
     */
    public function testDeleteBadPkFail(): void
    {
        $this->dataRefill();

        $rec = new SQLiteNameTestRecordBadPk();
        $rec->id = 75;
        $this->assertFalse($rec->getMapper()->xDeleteRecordByPk($rec));
    }

    /**
     * @throws MapperException
     */
    public function testDeleteNoPkFail(): void
    {
        $this->dataRefill();

        $rec = new SQLiteNameTestRecordNoPk();
        $rec->id = 85;
        $this->assertFalse($rec->getMapper()->xDeleteRecordByPk($rec));
    }

    /**
     * @throws MapperException
     */
    public function testCountEmpty(): void
    {
        $this->dataRefill();

        $rec = new SQLiteNameTestRecordNoPk();
        $rec->id = 95;
        $this->assertEmpty($rec->getMapper()->countRecord($rec));
    }

    /**
     * @throws MapperException
     */
    public function testLoadEmpty(): void
    {
        $this->dataRefill();

        $rec = new SQLiteNameTestRecordNoPk();
        $rec->id = 111;
        $this->assertEmpty($rec->getMapper()->loadMultiple($rec));
    }

    /**
     * @throws MapperException
     */
    protected function dataRefill(): void
    {
        $this->assertTrue($this->database->exec($this->dropTable(), []));
        $this->assertTrue($this->database->exec($this->basicTable(), []));
//        $this->assertTrue($this->database->exec($this->fillTable(), []));
//        $this->assertEquals(8, $this->database->rowCount());
    }

    protected function dropTable(): string
    {
        return 'DROP TABLE IF EXISTS "x_name_test"';
    }

    protected function basicTable(): string
    {
        return 'CREATE TABLE IF NOT EXISTS "x_name_test" (
  "x_id" INT AUTO_INCREMENT NOT NULL PRIMARY KEY ,
  "x_name" VARCHAR(20) NOT NULL
)';
    }
}


/**
 * Class SQLiteNameTestRecord
 * @property int id
 * @property string name
 */
class SQLiteNameTestRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('name', IEntryType::TYPE_STRING, 250);
        $this->setMapper('\StorageTests\Database\SQLiteTestMapper');
    }
}


class SQLiteTestMapper extends ADatabase
{
    use XAccess;

    protected function setMap(): void
    {
        $this->setSource('test_sqlite_local_test');
        $this->setTable('x_name_test');
        $this->setRelation('id', 'x_id');
        $this->setRelation('name', 'x_name');
        $this->addPrimaryKey('id');
    }
}


/**
 * Class SQLiteNameTestRecord
 * @property int id
 * @property string name
 */
class SQLiteNameTestRecordBadPk extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('name', IEntryType::TYPE_STRING, 250);
        $this->setMapper('\StorageTests\Database\SQLiteTestMapperBadPk');
    }
}


class SQLiteTestMapperBadPk extends ADatabase
{
    use XAccess;

    protected function setMap(): void
    {
        $this->setSource('test_sqlite_local_test');
        $this->setTable('x_name_test');
        $this->setRelation('id', 'x_id');
        $this->setRelation('name', 'x_name');
        $this->setRelation('out', 'x_off'); // intentionally not set in record
        $this->addPrimaryKey('out'); // intentionally non-existent and set as pk
    }
}


/**
 * Class SQLiteNameTestRecordNoPk
 * @property int id
 * @property string name
 */
class SQLiteNameTestRecordNoPk extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('name', IEntryType::TYPE_STRING, 250);
        $this->setMapper('\StorageTests\Database\SQLiteTestMapperNoPk');
    }
}


class SQLiteTestMapperNoPk extends ADatabase
{
    use XAccess;

    protected function setMap(): void
    {
        $this->setSource('test_sqlite_local_test');
        $this->setTable('x_name_test');
        $this->setRelation('id', 'x_id');
        $this->setRelation('name', 'x_name');
    }
}


trait XAccess
{
    public function xInsertRecord(ARecord $record): bool
    {
        return $this->insertRecord($record);
    }

    public function xUpdateRecord(ARecord $record): bool
    {
        return $this->updateRecord($record);
    }

    public function xUpdateRecordByPk(ARecord $record): bool
    {
        return $this->updateRecordByPk($record);
    }

    public function xLoadRecord(ARecord $record): bool
    {
        return $this->loadRecord($record);
    }

    public function xLoadRecordByPk(ARecord $record): bool
    {
        return $this->loadRecordByPk($record);
    }

    public function xDeleteRecord(ARecord $record): bool
    {
        return $this->deleteRecord($record);
    }

    public function xDeleteRecordByPk(ARecord $record): bool
    {
        return $this->deleteRecordByPk($record);
    }

    abstract protected function insertRecord(ARecord $record): bool;

    abstract protected function updateRecord(ARecord $record): bool;

    abstract protected function updateRecordByPk(ARecord $record): bool;

    abstract protected function loadRecord(ARecord $record): bool;

    abstract protected function loadRecordByPk(ARecord $record): bool;

    abstract protected function deleteRecord(ARecord $record): bool;

    abstract protected function deleteRecordByPk(ARecord $record): bool;
}

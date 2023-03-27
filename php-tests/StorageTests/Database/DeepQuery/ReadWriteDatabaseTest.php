<?php

namespace StorageTests\Database\DeepQuery;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Database\AReadWriteDatabase;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Storage\Database\Config;
use kalanis\kw_mapper\Storage\Database\ConfigStorage;
use kalanis\kw_mapper\Storage\Database\DatabaseSingleton;
use kalanis\kw_mapper\Storage\Database\PDO\SQLite;
use PDO;


/**
 * Class ReadWriteDatabaseTest
 * @package StorageTests\Database
 * @requires extension PDO
 * @requires extension pdo_sqlite
 */
class ReadWriteDatabaseTest extends CommonTestClass
{
    /** @var null|SQLite */
    protected $readDatabase = null;
    /** @var null|SQLite */
    protected $writeDatabase = null;

    /**
     * @throws MapperException
     */
    protected function setUp(): void
    {
        $readConf = Config::init()->setTarget(
            IDriverSources::TYPE_PDO_SQLITE,
            'test_sqlite_local_read_test',
            'file:memdb1?mode=memory&cache=shared',
            0,
            null,
            null,
            ''
        );
        $readConf->setParams(86000, true);

        $this->readDatabase = DatabaseSingleton::getInstance()->getDatabase($readConf);
        $this->readDatabase->addAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        ConfigStorage::getInstance()->addConfig($readConf);
        $writeConf = Config::init()->setTarget(
            IDriverSources::TYPE_PDO_SQLITE,
            'test_sqlite_local_write_test',
            'file:memdb2?mode=memory&cache=shared',
            0,
            null,
            null,
            ''
        );
        $writeConf->setParams(86000, true);
        ConfigStorage::getInstance()->addConfig($writeConf);

        $this->writeDatabase = DatabaseSingleton::getInstance()->getDatabase($readConf);
        $this->writeDatabase->addAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * @throws MapperException
     */
    public function testProcess(): void
    {
        $this->dataRefill();

        $rec = new SQLiteBasicTestRecord();
        $this->assertNotEmpty($rec->getMapper()->getAlias());

        $rec->id = 15;
        $rec->name = 'store';
        $this->assertTrue($rec->getMapper()->xInsertRecord($rec));

        $rec->getEntry('id')->setData(15, true); // intentionally re-set as already known
        $rec->name = 'update';
        $this->assertTrue($rec->getMapper()->xUpdateRecord($rec));

        $del = new SQLiteBasicTestRecord();
        $del->id = 15;
        $this->assertTrue($del->getMapper()->xDeleteRecord($del));
    }

    /**
     * @throws MapperException
     */
    public function testProcessNoPk(): void
    {
        $this->dataRefill();

        $rec = new SQLiteBasicTestRecordNoPk();
        $this->assertNotEmpty($rec->getMapper()->getAlias());

        $rec->id = 7777;
        $rec->name = 'store';
        $this->assertTrue($rec->getMapper()->xInsertRecord($rec));

        $rec->getEntry('id')->setData(7777, true); // intentionally re-set as already known
        $rec->name = 'update';
        $this->assertTrue($rec->getMapper()->xUpdateRecord($rec));

        $upd = new SQLiteBasicTestRecordNoPk();
        $upd->getEntry('id')->setData(7777, true);
        $upd->name = 'unstore';
        $this->assertTrue($upd->getMapper()->xUpdateRecord($upd));

        $del = new SQLiteBasicTestRecordNoPk();
        $del->id = 7777;
        $this->assertTrue($del->getMapper()->xDeleteRecord($del));
    }

    /**
     * @throws MapperException
     */
    public function testInsertNoProperties(): void
    {
        $this->dataRefill();

        $rec = new SQLiteBasicTestRecord();
        $this->assertFalse($rec->getMapper()->xInsertRecord($rec));
    }

    /**
     * @throws MapperException
     */
    public function testUpdateNoConditions(): void
    {
        $this->dataRefill();

        $rec = new SQLiteBasicTestRecord();
        $rec->getEntry('id')->setData(15, true);
        $rec->getEntry('name')->setData('out', true);
        $this->assertFalse($rec->getMapper()->xUpdateRecord($rec));
    }

    /**
     * @throws MapperException
     */
    public function testUpdateNoProperties(): void
    {
        $this->dataRefill();

        $rec = new SQLiteBasicTestRecord();
        $rec->getEntry('id')->setData(15);
        $rec->getEntry('name')->setData('out');
        $this->assertFalse($rec->getMapper()->xUpdateRecord($rec));
    }

    /**
     * @throws MapperException
     */
    public function testUpdateBadPkFail(): void
    {
        $this->dataRefill();

        $rec = new SQLiteBasicTestRecordBadPk();
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

        $rec = new SQLiteBasicTestRecordNoPk();
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

        $rec = new SQLiteBasicTestRecord();
        $rec->id = 35;
        $this->assertFalse($rec->getMapper()->xLoadRecord($rec));

        $next = new SQLiteBasicTestRecordBadPk();
        $this->assertFalse($next->getMapper()->xLoadRecord($next));
    }

    /**
     * @throws MapperException
     */
    public function testLoadBadPkFail(): void
    {
        $this->dataRefill();

        $rec = new SQLiteBasicTestRecordBadPk();
        $rec->id = 45;
        $this->assertFalse($rec->getMapper()->xLoadRecordByPk($rec));
    }

    /**
     * @throws MapperException
     */
    public function testLoadNoPkFail(): void
    {
        $this->dataRefill();

        $rec = new SQLiteBasicTestRecordNoPk();
        $rec->id = 55;
        $this->assertFalse($rec->getMapper()->xLoadRecordByPk($rec));
    }

    /**
     * @throws MapperException
     */
    public function testDeletePass(): void
    {
        $this->dataRefill();

        $rec = new SQLiteBasicTestRecord();
        $rec->id = 65;
        $this->assertTrue($rec->getMapper()->xDeleteRecord($rec));
        $this->assertTrue($rec->getMapper()->xDeleteRecordByPk($rec));
    }

    /**
     * @throws MapperException
     */
    public function testDeleteNoConditions(): void
    {
        $this->dataRefill();

        $rec = new SQLiteBasicTestRecord();
        $this->assertFalse($rec->getMapper()->xDeleteRecord($rec));

        $rec = new SQLiteBasicTestRecordNoPk();
        $this->assertFalse($rec->getMapper()->xDeleteRecordByPk($rec));
    }

    /**
     * @throws MapperException
     */
    public function testDeleteBadPkFail(): void
    {
        $this->dataRefill();

        $rec = new SQLiteBasicTestRecordBadPk();
        $rec->id = 75;
        $this->assertFalse($rec->getMapper()->xDeleteRecordByPk($rec));
    }

    /**
     * @throws MapperException
     */
    public function testDeleteNoPkFail(): void
    {
        $this->dataRefill();

        $rec = new SQLiteBasicTestRecordNoPk();
        $rec->id = 85;
        $this->assertFalse($rec->getMapper()->xDeleteRecordByPk($rec));
    }

    /**
     * @throws MapperException
     */
    public function testCountEmpty(): void
    {
        $this->dataRefill();

        $rec = new SQLiteBasicTestRecordNoPk();
        $rec->id = 95;
        $this->assertEmpty($rec->getMapper()->countRecord($rec));

        $next = new SQLiteBasicTestRecordNoPk();
        $this->assertEmpty($next->getMapper()->countRecord($next));

        $last = new SQLiteBasicTestRecord();
        $last->name = 'odd';
        $this->assertEmpty($last->getMapper()->countRecord($last));
    }

    /**
     * @throws MapperException
     */
    public function testLoadEmpty(): void
    {
        $this->dataRefill();

        $store1 = new SQLiteBasicTestRecordNoPk();
        $store1->id = 22;
        $store1->name = 'ijn';
        $this->assertTrue($store1->save(true));

        $store2 = new SQLiteBasicTestRecordNoPk();
        $store2->id = 16;
        $store2->name = 'ijn';
        $this->assertTrue($store2->save(true));

        $rec = new SQLiteBasicTestRecordNoPk();
        $rec->id = 111;
        $this->assertEmpty($rec->getMapper()->loadMultiple($rec));

        $next = new SQLiteBasicTestRecordNoPk();
        $this->assertEmpty($next->getMapper()->loadMultiple($next));

        $next = new SQLiteStoreTestRecord();
        $next->name = 'wat';
        $this->assertEmpty($next->getMapper()->xLoadRecordByPk($next));
    }

    /**
     * @throws MapperException
     */
    public function testLoad(): void
    {
        $this->dataRefill();

        $store1 = new SQLiteStoreTestRecord();
        $store1->id = 22;
        $store1->name = 'ijn';
        $this->assertTrue($store1->save(true));

        $store2 = new SQLiteStoreTestRecord();
        $store2->id = 16;
        $store2->name = 'ijn';
        $this->assertTrue($store2->save(true));

        $rec = new SQLiteBasicTestRecord();
        $rec->id = 16;
        $this->assertNotEmpty($rec->getMapper()->load($rec));

        $rec = new SQLiteBasicTestRecordNoPk();
        $rec->id = 22;
        $this->assertNotEmpty($rec->getMapper()->load($rec));

        $next = new SQLiteBasicTestRecordNoPk();
        $next->name = 'ijn';
        $this->assertNotEmpty($next->getMapper()->loadMultiple($next));
    }

    /**
     * @throws MapperException
     */
    protected function dataRefill(): void
    {
        $this->assertTrue($this->readDatabase->exec($this->dropTable(), []));
        $this->assertTrue($this->readDatabase->exec($this->basicTable(), []));
        $this->assertTrue($this->writeDatabase->exec($this->dropTable(), []));
        $this->assertTrue($this->writeDatabase->exec($this->basicTable(), []));
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
 * Class SQLiteBasicTestRecord
 * @property int $id
 * @property string $name
 * This one read from different storage than write
 */
class SQLiteBasicTestRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('name', IEntryType::TYPE_STRING, 250);
        $this->setMapper(SQLiteBasicTestMapper::class);
    }
}


class SQLiteBasicTestMapper extends AReadWriteDatabase
{
    use XAccess;

    protected function setMap(): void
    {
        $this->setReadSource('test_sqlite_local_read_test');
        $this->setWriteSource('test_sqlite_local_write_test');
        $this->setTable('x_name_test');
        $this->setRelation('id', 'x_id');
        $this->setRelation('name', 'x_name');
        $this->addPrimaryKey('id');
    }
}


/**
 * Class SQLiteBasicTestRecord
 * @property int $id
 * @property string $name
 * This one can write to that readable source
 */
class SQLiteStoreTestRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('name', IEntryType::TYPE_STRING, 250);
        $this->setMapper(SQLiteStoreTestMapper::class);
    }
}


class SQLiteStoreTestMapper extends AReadWriteDatabase
{
    use XAccess;

    protected function setMap(): void
    {
        $this->setReadSource('test_sqlite_local_read_test');
        $this->setWriteSource('test_sqlite_local_read_test'); // intentionally both has the same target
        $this->setTable('x_name_test');
        $this->setRelation('id', 'x_id');
        $this->setRelation('name', 'x_name');
        $this->addPrimaryKey('id');
    }
}


/**
 * Class SQLiteNameTestRecord
 * @property int $id
 * @property string $name
 */
class SQLiteBasicTestRecordBadPk extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('name', IEntryType::TYPE_STRING, 250);
        $this->setMapper(SQLiteBasicTestMapperBadPk::class);
    }
}


class SQLiteBasicTestMapperBadPk extends AReadWriteDatabase
{
    use XAccess;

    protected function setMap(): void
    {
        $this->setReadSource('test_sqlite_local_read_test');
        $this->setWriteSource('test_sqlite_local_write_test');
        $this->setTable('x_name_test');
        $this->setRelation('id', 'x_id');
        $this->setRelation('name', 'x_name');
        $this->setRelation('out', 'x_off'); // intentionally not set in record
        $this->addPrimaryKey('out'); // intentionally non-existent and set as pk
    }
}


/**
 * Class SQLiteBasicTestRecordNoPk
 * @property int $id
 * @property string $name
 */
class SQLiteBasicTestRecordNoPk extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('name', IEntryType::TYPE_STRING, 250);
        $this->setMapper(SQLiteTestMapperNoPk::class);
    }
}


class SQLiteTestMapperNoPk extends AReadWriteDatabase
{
    use XAccess;

    protected function setMap(): void
    {
        $this->setReadSource('test_sqlite_local_read_test');
        $this->setWriteSource('test_sqlite_local_write_test');
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

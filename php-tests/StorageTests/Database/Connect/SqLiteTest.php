<?php

namespace StorageTests\Database\Connect;


use Builder;
use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Database\ADatabase;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Search\Search;
use kalanis\kw_mapper\Storage\Database\Config;
use kalanis\kw_mapper\Storage\Database\ConfigStorage;
use kalanis\kw_mapper\Storage\Database\DatabaseSingleton;
use kalanis\kw_mapper\Storage\Database\Dialects;
use kalanis\kw_mapper\Storage\Database\PDO\SQLite;
use PDO;


/**
 * Class SqLiteTest
 * @package StorageTests\Database\Connect
 * @requires extension PDO
 * @requires extension pdo_sqlite
 */
class SqLiteTest extends CommonTestClass
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
                    'test_sqlite_local',
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
     * Beware - SQLite cannot do more than one query at time - so the semicolons are unnecessary
     */
    public function testProcess(): void
    {
        $this->database->reconnect();
        $this->assertFalse($this->database->exec('', []));
        $this->database->reconnect();
        $this->assertEmpty($this->database->query('', []));

        $this->dataRefill();

        $query = new Builder();
        $query->setBaseTable('d_queued_commands');
        $sql = new Dialects\SQLite();
        $result = $this->database->query($sql->describe($query), []);
//var_dump($result);
        $this->assertNotEmpty($result, 'There MUST be table from file!');

        $query->addColumn('d_queued_commands', 'qc_id');
        $query->addColumn('d_queued_commands', 'qc_status');
        $lines = $this->database->query($sql->select($query), $query->getParams());
        $this->assertEquals(6, count($lines));
//var_dump(['full dump' => $lines]);
        $query->addCondition('d_queued_commands', 'qc_time_start', IQueryBuilder::OPERATION_EQ, 123456);
//var_dump(['query dump' => str_split($sql->select($query), 120)]);
        $lines = $this->database->query($sql->select($query), $query->getParams());
        $this->assertEquals(5, count($lines));

        $this->assertTrue($this->database->beginTransaction());
        $this->database->exec('INSERT INTO `d_queued_commands` (qc_id, qc_time_start, qc_time_end, qc_status, qc_command) VALUES (11, 123456, 123456789, 13, "ls -laf");', []);
        $this->assertTrue($this->database->commit());
        $this->assertNotEmpty($this->database->lastInsertId(), 'There must be last id!');
        $this->assertEquals(1, $this->database->rowCount());
        $this->assertTrue($this->database->beginTransaction());
        $this->database->exec('INSERT INTO `d_queued_commands` (qc_id, qc_time_start, qc_time_end, qc_status, qc_command) VALUES (12, 1234567, 123456789, 13, "ls -laf");', []);
        $this->assertTrue($this->database->rollBack());

        $lines = $this->database->query($sql->select($query), $query->getParams());
        $this->assertEquals(6, count($lines));
    }

    /**
     * @throws MapperException
     */
    public function testMapped(): void
    {
        $this->dataRefill();

        // now queries - search
        $search = new Search(new SQLiteTestRecord());
        $search->like('command', '%laf%');
        $this->assertEquals(4, $search->getCount());

        $records = $search->getResults();
        $this->assertEquals(4, count($records));

        /** @var SQLiteTestRecord $record */
        $record = reset($records);
        $this->assertEquals(5, $record->id);
        $this->assertEquals(123456, $record->timeStart);
        $this->assertEquals(12345678, $record->timeEnd);
        $this->assertEquals(5, $record->status);

        $search2 = new Search(new SQLiteTestRecord());
        $search2->exact('status', 55);
        $this->assertEquals(0, $search2->getCount());
        $this->assertEquals(0, count($search2->getResults()));
    }

    /**
     * @throws MapperException
     */
    public function testCrud(): void
    {
        $this->dataRefill();

        // create
        $rec1 = new SQLiteTestRecord();
        $rec1->id = 14;
        $rec1->timeStart = 12345;
        $rec1->timeEnd = 1234567;
        $rec1->status = 8;
        $this->assertTrue($rec1->save(true));

        // read
        $rec2 = new SQLiteTestRecord();
        $rec2->status = 8;
        $this->assertEquals(1, count($rec2->loadMultiple()));

        $this->assertTrue($rec2->load());
        $this->assertEquals(12345, $rec2->timeStart);
        $this->assertEquals(1234567, $rec2->timeEnd);
        // update
        $rec2->status = 9;
        $this->assertTrue($rec2->save());

        $rec3 = new SQLiteTestRecord();
        $rec3->status = 8;
        $this->assertEquals(0, $rec3->count());

        $rec4 = new SQLiteTestRecord();
        $rec4->id = 6;
        $this->assertTrue($rec4->load());
        $this->assertEquals(1234567, $rec4->timeStart);
        $this->assertEquals(12345678, $rec4->timeEnd);

        // delete
        $rec5 = new SQLiteTestRecord();
        $rec5->status = 9;
        $this->assertTrue($rec5->delete());

        // bulk update - for now via ugly hack
        $rec6 = new SQLiteTestRecord();
        $rec6->getEntry('status')->setData(5, true); // hack to set condition
        $rec6->timeEnd = 123; // this will be updated
        $rec6->getMapper()->update($rec6); // todo: another hack, change rules for insert/update in future
    }

    protected function dataRefill(): void
    {
        $this->assertTrue($this->database->exec($this->dropTable(), []));
        $this->assertTrue($this->database->exec($this->basicTable(), []));
        $this->assertTrue($this->database->exec($this->fillTable(), []));
        $this->assertEquals(6, $this->database->rowCount());
    }

    protected function dropTable(): string
    {
        return 'DROP TABLE IF EXISTS "d_queued_commands"';
    }

    protected function basicTable(): string
    {
        return 'CREATE TABLE IF NOT EXISTS "d_queued_commands" (
  "qc_id" INT AUTO_INCREMENT NOT NULL PRIMARY KEY ,
  "qc_time_start" VARCHAR(20) NULL,
  "qc_time_end" VARCHAR(20) NULL,
  "qc_status" INT(1) NULL,
  "qc_command" TEXT NULL
);';
    }

    protected function fillTable(): string
    {
        return 'INSERT INTO "d_queued_commands" ("qc_id", "qc_time_start", "qc_time_end", "qc_status", "qc_command") VALUES
( 5, 123456,  12345678,  5, "ls -laf"),
( 6, 1234567, 12345678,  5, "ls -laf"),
( 7, 123456,  12345678, 11, "ls -laf"),
( 8, 123456,  12345678, 11, "ls -laf"),
( 9, 123456,  12345678, 12, "ls -alF"),
(10, 123456,  12345678, 14, null)
;';
    }
}


/**
 * Class SQLiteTestRecord
 * @property int id
 * @property int timeStart
 * @property int timeEnd
 * @property int status
 * @property string command
 */
class SQLiteTestRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('timeStart', IEntryType::TYPE_INTEGER, 99999999);
        $this->addEntry('timeEnd', IEntryType::TYPE_INTEGER, 99999999);
        $this->addEntry('status', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('command', IEntryType::TYPE_STRING, 250);
        $this->setMapper('\StorageTests\Database\Connect\SQLiteTestMapper');
    }
}


class SQLiteTestMapper extends ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('test_sqlite_local');
        $this->setTable('d_queued_commands');
        $this->setRelation('id', 'qc_id');
        $this->setRelation('timeStart', 'qc_time_start');
        $this->setRelation('timeEnd', 'qc_time_end');
        $this->setRelation('status', 'qc_status');
        $this->setRelation('command', 'qc_command');
        $this->addPrimaryKey('id');
    }
}

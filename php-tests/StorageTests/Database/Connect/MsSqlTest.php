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
use kalanis\kw_mapper\Storage\Database\PDO\MSSQL;
use PDO;


/**
 * Class MySqlTest
 * @package StorageTests\Database\Connect
 * @requires extension PDO
 * @requires extension pdo_mssql
 * @link https://sqliteonline.com/
 */
class MsSqlTest extends CommonTestClass
{
    /** @var null|MSSQL */
    protected $database = null;
    /** @var bool */
    protected $skipIt = false;

    /**
     * @throws MapperException
     */
    protected function setUp(): void
    {
        $skipIt = getenv('SKIP_TSQL_DB_TESTS');
        $this->skipIt = false !== $skipIt && boolval(intval(strval($skipIt)));

        $location = getenv('KW_MAPPER_TSQL_DB_HOST');
        $location = false !== $location ? strval($location) : '127.0.0.1' ;

        $port = getenv('KW_MAPPER_TSQL_DB_PORT');
        $port = false !== $port ? intval($port) : 3306 ;

        $user = getenv('KW_MAPPER_TSQL_DB_USER');
        $user = false !== $user ? strval($user) : 'testing' ;

        $pass = getenv('KW_MAPPER_TSQL_DB_PASS');
        $pass = false !== $pass ? strval($pass) : 'testing' ;

        $db = getenv('KW_MAPPER_TSQL_DB_NAME');
        $db = false !== $db ? strval($db) : 'testing' ;

        $conf = Config::init()->setTarget(
                    IDriverSources::TYPE_PDO_MSSQL,
                    'test_mssql_local',
                    $location,
                    $port,
                    $user,
                    $pass,
                    $db
                );
        $conf->setParams(2400, true);
        ConfigStorage::getInstance()->addConfig($conf);
        $this->database = DatabaseSingleton::getInstance()->getDatabase($conf);
        $this->database->addAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * @throws MapperException
     */
    public function testProcess(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->database->reconnect();
        $this->assertFalse($this->database->exec('', []));
        $this->database->reconnect();
        $this->assertEmpty($this->database->query('', []));

        $this->dataRefill();

        $query = new Builder();
        $query->setBaseTable('d_queued_commands');
        $sql = new Dialects\TransactSQL();
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
        $this->database->exec('INSERT INTO "d_queued_commands" ("qc_id", "qc_time_start", "qc_time_end", "qc_status", "qc_command") VALUES (11, 123456, 123456789, 13, \'ls -laf\');', []);
        $this->assertTrue($this->database->commit());
        $this->assertNotEmpty($this->database->lastInsertId(), 'There must be last id!');
        $this->assertEquals(1, $this->database->rowCount());
        $this->assertTrue($this->database->beginTransaction());
        $this->database->exec('INSERT INTO "d_queued_commands" ("qc_id", "qc_time_start", "qc_time_end", "qc_status", "qc_command") VALUES (12, 1234567, 123456789, 13, \'ls -laf\');', []);
        $this->assertTrue($this->database->rollBack());

        $lines = $this->database->query($sql->select($query), $query->getParams());
        $this->assertEquals(6, count($lines));
    }

    /**
     * @throws MapperException
     */
    public function testMapped(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        // now queries - search
        $search = new Search(new MsSqlTestRecord());
        $search->like('command', '%laf%');
        $this->assertEquals(4, $search->getCount());

        $records = $search->getResults();
        $this->assertEquals(4, count($records));

        /** @var MsSqlTestRecord $record */
        $record = reset($records);
        $this->assertEquals(5, $record->id);
        $this->assertEquals(123456, $record->timeStart);
        $this->assertEquals(12345678, $record->timeEnd);
        $this->assertEquals(5, $record->status);

        $search2 = new Search(new MsSqlTestRecord());
        $search2->exact('status', 55);
        $this->assertEquals(0, $search2->getCount());
        $this->assertEquals(0, count($search2->getResults()));
    }

    /**
     * @throws MapperException
     */
    public function testCrud(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        // create
        $rec1 = new MsSqlTestRecord();
        $rec1->id = 14;
        $rec1->timeStart = 12345;
        $rec1->timeEnd = 1234567;
        $rec1->status = 8;
        $this->assertTrue($rec1->save(true));

        // read
        $rec2 = new MsSqlTestRecord();
        $rec2->status = 8;
        $this->assertEquals(1, count($rec2->loadMultiple()));

        $this->assertTrue($rec2->load());
        $this->assertEquals(12345, $rec2->timeStart);
        $this->assertEquals(1234567, $rec2->timeEnd);
        // update
        $rec2->status = 9;
        $this->assertTrue($rec2->save());

        $rec3 = new MsSqlTestRecord();
        $rec3->status = 8;
        $this->assertEquals(0, $rec3->count());

        $rec4 = new MsSqlTestRecord();
        $rec4->id = 6;
        $this->assertTrue($rec4->load());
        $this->assertEquals(1234567, $rec4->timeStart);
        $this->assertEquals(12345678, $rec4->timeEnd);

        // delete
        $rec5 = new MsSqlTestRecord();
        $rec5->status = 9;
        $this->assertTrue($rec5->delete());

        // bulk update - for now via ugly hack
        $rec6 = new MsSqlTestRecord();
        $rec6->getEntry('status')->setData(5, true); // hack to set condition
        $rec6->timeEnd = 123; // this will be updated
        $rec6->getMapper()->update($rec6); // todo: another hack, change rules for insert/update in future
    }

    /**
     * @throws MapperException
     */
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
        return 'CREATE TABLE "d_queued_commands" (
  "qc_id" INT PRIMARY KEY NOT NULL,
  "qc_time_start" VARCHAR(20) NULL,
  "qc_time_end" VARCHAR(20) NULL,
  "qc_status" INT NULL,
  "qc_command" VARCHAR(256) NULL
)';
    }

    protected function fillTable(): string
    {
        return 'INSERT INTO "d_queued_commands" ("qc_id", "qc_time_start", "qc_time_end", "qc_status", "qc_command") VALUES
( 5, 123456,  12345678,  5, \'ls -laf\'),
( 6, 1234567, 12345678,  5, \'ls -laf\'),
( 7, 123456,  12345678, 11, \'ls -laf\'),
( 8, 123456,  12345678, 11, \'ls -laf\'),
( 9, 123456,  12345678, 12, \'ls -alF\'),
(10, 123456,  12345678, 14, null)
';
    }
}


/**
 * Class MsSqlTestRecord
 * @property int $id
 * @property int $timeStart
 * @property int $timeEnd
 * @property int $status
 * @property string $command
 */
class MsSqlTestRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('timeStart', IEntryType::TYPE_INTEGER, 99999999);
        $this->addEntry('timeEnd', IEntryType::TYPE_INTEGER, 99999999);
        $this->addEntry('status', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('command', IEntryType::TYPE_STRING, 250);
        $this->setMapper(MsSqlTestMapper::class);
    }
}


class MsSqlTestMapper extends ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('test_mssql_local');
        $this->setTable('d_queued_commands');
        $this->setRelation('id', 'qc_id');
        $this->setRelation('timeStart', 'qc_time_start');
        $this->setRelation('timeEnd', 'qc_time_end');
        $this->setRelation('status', 'qc_status');
        $this->setRelation('command', 'qc_command');
        $this->addPrimaryKey('id');
    }
}

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
use kalanis\kw_mapper\Storage\Database\PDO\PostgreSQL;
use PDO;


/**
 * Class PostgresTest
 * @package StorageTests\Database\Connect
 * @requires extension PDO
 * @requires extension pdo_pgsql
 */
class PostgresTest extends CommonTestClass
{
    /** @var null|PostgreSQL */
    protected $database = null;
    /** @var bool */
    protected $skipIt = false;

    /**
     * @throws MapperException
     * Beware quoting in queries - Postgres uses simple for strings and double for references!
     */
    protected function setUp(): void
    {
        $skipIt = getenv('PGSKIP');
        $this->skipIt = false !== $skipIt && boolval(intval(strval($skipIt)));

        $location = getenv('PGSERVER');
        $location = false !== $location ? strval($location) : '127.0.0.1' ;

        $port = getenv('PGPORT');
        $port = false !== $port ? intval($port) : 5432 ;

        $user = getenv('PGUSER');
        $user = false !== $user ? strval($user) : 'postgres' ;

        $pass = getenv('PGPASS');
        $pass = false !== $pass ? strval($pass) : 'postgres' ;

        $db = getenv('PGDB');
        $db = false !== $db ? strval($db) : 'testing' ;

        $conf = Config::init()->setTarget(
                    IDriverSources::TYPE_PDO_POSTGRES,
                    'test_postgres_local',
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
        $sql = new Dialects\PostgreSQL();
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
        $search = new Search(new PostgresTestRecord());
        $search->like('command', '%laf%');
        $this->assertEquals(4, $search->getCount());

        $records = $search->getResults();
        $this->assertEquals(4, count($records));

        /** @var PostgresTestRecord $record */
        $record = reset($records);
        $this->assertEquals(5, $record->id);
        $this->assertEquals(123456, $record->time_start);
        $this->assertEquals(12345678, $record->time_end);
        $this->assertEquals(5, $record->status);

        $search2 = new Search(new PostgresTestRecord());
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
        $rec1 = new PostgresTestRecord();
        $rec1->id = 14;
        $rec1->time_start = 12345;
        $rec1->time_end = 1234567;
        $rec1->status = 8;
        $this->assertTrue($rec1->save(true));

        // read
        $rec2 = new PostgresTestRecord();
        $rec2->status = 8;
        $this->assertEquals(1, count($rec2->loadMultiple()));

        $this->assertTrue($rec2->load());
        $this->assertEquals(12345, $rec2->time_start);
        $this->assertEquals(1234567, $rec2->time_end);
        // update
        $rec2->status = 9;
        $this->assertTrue($rec2->save());

        $rec3 = new PostgresTestRecord();
        $rec3->status = 8;
        $this->assertEquals(0, $rec3->count());

        $rec4 = new PostgresTestRecord();
        $rec4->id = 6;
        $this->assertTrue($rec4->load());
        $this->assertEquals(1234567, $rec4->time_start);
        $this->assertEquals(12345678, $rec4->time_end);

        // delete
        $rec5 = new PostgresTestRecord();
        $rec5->status = 9;
        $this->assertTrue($rec5->delete());

        // bulk update - for now via ugly hack
        $rec6 = new PostgresTestRecord();
        $rec6->getEntry('status')->setData(5, true); // hack to set condition
        $rec6->time_end = 123; // this will be updated
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
        return 'CREATE UNLOGGED TABLE IF NOT EXISTS "d_queued_commands" (
  "qc_id" SERIAL PRIMARY KEY,
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
 * Class PostgresTestRecord
 * @property int $id
 * @property int $time_start
 * @property int $time_end
 * @property int $status
 * @property string $command
 */
class PostgresTestRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('time_start', IEntryType::TYPE_INTEGER, 99999999);
        $this->addEntry('time_end', IEntryType::TYPE_INTEGER, 99999999);
        $this->addEntry('status', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('command', IEntryType::TYPE_STRING, 250);
        $this->setMapper(PostgresTestMapper::class);
    }
}


class PostgresTestMapper extends ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('test_postgres_local');
        $this->setTable('d_queued_commands');
        $this->setRelation('id', 'qc_id');
        $this->setRelation('time_start', 'qc_time_start');
        $this->setRelation('time_end', 'qc_time_end');
        $this->setRelation('status', 'qc_status');
        $this->setRelation('command', 'qc_command');
        $this->addPrimaryKey('id');
    }
}

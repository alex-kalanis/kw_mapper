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
use kalanis\kw_mapper\Storage\Database\PDO\MySQL;
use PDO;


/**
 * Class MySqlTest
 * @package StorageTests\Database\Connect
 * @requires extension PDO
 * @requires extension pdo_mysql
 */
class MySqlTest extends CommonTestClass
{
    /** @var null|MySQL */
    protected $database = null;
    /** @var bool */
    protected $skipIt = false;

    /**
     * @throws MapperException
     */
    protected function setUp(): void
    {
        $skipIt = getenv('MYSKIP');
        $this->skipIt = false !== $skipIt && boolval(intval(strval($skipIt)));

        $location = getenv('MYSERVER');
        $location = false !== $location ? strval($location) : '127.0.0.1' ;

        $port = getenv('MYPORT');
        $port = false !== $port ? intval($port) : 3306 ;

        $user = getenv('MYUSER');
        $user = false !== $user ? strval($user) : 'testing' ;

        $pass = getenv('MYPASS');
        $pass = false !== $pass ? strval($pass) : 'testing' ;

        $db = getenv('MYDB');
        $db = false !== $db ? strval($db) : 'testing' ;

        $conf = Config::init()->setTarget(
                    IDriverSources::TYPE_PDO_MYSQL,
                    'test_mysql_local',
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

        if (in_array($user, ['root', ])) {
            $this->database->reconnect();
            $this->database->exec('CREATE DATABASE IF NOT EXISTS ' . strval($db), []);
        }
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
        $sql = new Dialects\MySQL();
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
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        // now queries - search
        $search = new Search(new MySqlTestRecord());
        $search->like('command', '%laf%');
        $this->assertEquals(4, $search->getCount());

        $records = $search->getResults();
        $this->assertEquals(4, count($records));

        /** @var MySqlTestRecord $record */
        $record = reset($records);
        $this->assertEquals(5, $record->id);
        $this->assertEquals(123456, $record->timeStart);
        $this->assertEquals(12345678, $record->timeEnd);
        $this->assertEquals(5, $record->status);

        $search2 = new Search(new MySqlTestRecord());
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
        $rec1 = new MySqlTestRecord();
        $rec1->id = 14;
        $rec1->timeStart = 12345;
        $rec1->timeEnd = 1234567;
        $rec1->status = 8;
        $this->assertTrue($rec1->save(true));

        // read
        $rec2 = new MySqlTestRecord();
        $rec2->status = 8;
        $this->assertEquals(1, count($rec2->loadMultiple()));

        $this->assertTrue($rec2->load());
        $this->assertEquals(12345, $rec2->timeStart);
        $this->assertEquals(1234567, $rec2->timeEnd);
        // update
        $rec2->status = 9;
        $this->assertTrue($rec2->save());

        $rec3 = new MySqlTestRecord();
        $rec3->status = 8;
        $this->assertEquals(0, $rec3->count());

        $rec4 = new MySqlTestRecord();
        $rec4->id = 6;
        $this->assertTrue($rec4->load());
        $this->assertEquals(1234567, $rec4->timeStart);
        $this->assertEquals(12345678, $rec4->timeEnd);

        // delete
        $rec5 = new MySqlTestRecord();
        $rec5->status = 9;
        $this->assertTrue($rec5->delete());

        // bulk update - for now via ugly hack
        $rec6 = new MySqlTestRecord();
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
        return 'DROP TABLE IF EXISTS `d_queued_commands`';
    }

    protected function basicTable(): string
    {
        return 'CREATE TABLE IF NOT EXISTS `d_queued_commands` (
  `qc_id` INT AUTO_INCREMENT NOT NULL PRIMARY KEY ,
  `qc_time_start` VARCHAR(20) NULL,
  `qc_time_end` VARCHAR(20) NULL,
  `qc_status` INT(1) NULL,
  `qc_command` TEXT NULL
)';
    }

    protected function fillTable(): string
    {
        return 'INSERT INTO `d_queued_commands` (`qc_id`, `qc_time_start`, `qc_time_end`, `qc_status`, `qc_command`) VALUES
( 5, 123456,  12345678,  5, "ls -laf"),
( 6, 1234567, 12345678,  5, "ls -laf"),
( 7, 123456,  12345678, 11, "ls -laf"),
( 8, 123456,  12345678, 11, "ls -laf"),
( 9, 123456,  12345678, 12, "ls -alF"),
(10, 123456,  12345678, 14, null)
';
    }
}


/**
 * Class MySqlTestRecord
 * @property int id
 * @property int timeStart
 * @property int timeEnd
 * @property int status
 * @property string command
 */
class MySqlTestRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('timeStart', IEntryType::TYPE_INTEGER, 99999999);
        $this->addEntry('timeEnd', IEntryType::TYPE_INTEGER, 99999999);
        $this->addEntry('status', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('command', IEntryType::TYPE_STRING, 250);
        $this->setMapper('\StorageTests\Database\Connect\MySqlTestMapper');
    }
}


class MySqlTestMapper extends ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('test_mysql_local');
        $this->setTable('d_queued_commands');
        $this->setRelation('id', 'qc_id');
        $this->setRelation('timeStart', 'qc_time_start');
        $this->setRelation('timeEnd', 'qc_time_end');
        $this->setRelation('status', 'qc_status');
        $this->setRelation('command', 'qc_command');
        $this->addPrimaryKey('id');
    }
}

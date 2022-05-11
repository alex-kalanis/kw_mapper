<?php

namespace StorageTests\Database\DeepQuery;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Database\ADatabase;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Search\Search;
use kalanis\kw_mapper\Storage\Database\Config;
use kalanis\kw_mapper\Storage\Database\ConfigStorage;
use kalanis\kw_mapper\Storage\Database\DatabaseSingleton;
use kalanis\kw_mapper\Storage\Database\PDO\SQLite;
use PDO;


/**
 * Class SqLiteCircularTest
 * @package StorageTests\Database\DeepQuery
 * @requires extension PDO
 * @requires extension pdo_sqlite
 */
class SqLiteCircularTest extends CommonTestClass
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
     */
    public function testSimpleData(): void
    {
        $this->dataRefill();

        $search = new Search(new SQLiteNameTestRecord());
        $search->exact('name', 'Evgenij');
        $this->assertEquals(1, $search->getCount());
        $records = $search->getResults();
        $record = reset($records);
        $this->assertEquals(18, $record->id);
        $this->assertEquals('Evgenij', $record->name);
        $this->assertEmpty($record->pars);
    }

    /**
     * @throws MapperException
     * these records has common sub-record
     */
    public function testFirstLevelData(): void
    {
        $this->dataRefill();

        $search = new Search(new SQLiteNameTestRecord());
        $search->child('pars', '', '', 'chil'); // need own alias due table name
        $search->exact('chil.name', 'Jakub');
        $this->assertEquals(3, $search->getCount());

        $records = $search->getResults();
        $record = reset($records);
        $this->assertNotEmpty($record->pars);
        $parents = $record->pars;
        $parent = reset($parents);

        $this->assertEquals(4, $parent->id);
        $this->assertEquals('Jakub', $parent->name);
        $this->assertEquals(18, $parent->par);
        $this->assertEmpty($parent->pars);

        $record = next($records);
        $this->assertNotEmpty($record->pars);
        $parents = $record->pars;
        $this->assertEquals($parent, reset($parents));

        $record = next($records);
        $this->assertNotEmpty($record->pars);
        $parents = $record->pars;
        $this->assertEquals($parent, reset($parents));
    }

    /**
     * @throws MapperException
     */
    public function testSecondLevelData(): void
    {
        $this->dataRefill();

        $search = new Search(new SQLiteNameTestRecord());
        $search->child('pars', '', '', 'chil');
        $search->child('pars', '', 'chil', 'deep');
        $search->exact('deep.name', 'Radek');
        $this->assertEquals(1, $search->getCount());

        $records = $search->getResults();
        $record = reset($records);
        $this->assertEquals(14, $record->id);
        $this->assertEquals('Zbynek', $record->name);
    }

    /**
     * @throws MapperException
     */
    public function testNonExistingChild(): void
    {
        $this->dataRefill();

        $search = new Search(new SQLiteNameTestRecord());
        $this->expectException(MapperException::class); // sqlite does not know left outer join here
        $search->childNotExist('pars', 'par');
//        $this->assertEquals(2, $search->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testConnectChildNotParent(): void
    {
        $search = new Search(new SQLiteNameTestRecord());
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Unknown record for parent alias *not-known*');
        $search->child('chld', '', 'not-known');
    }

    /**
     * @throws MapperException
     */
    public function testConnectChildNotParentKey(): void
    {
        $search = new Search(new SQLiteNameTestRecord());
        $search->child('pars', '', '', 'chil');
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Unknown alias *deep* in mapper for parent *chil*');
        $search->child('deep', '', 'chil', 'deep');
    }

    /**
     * @throws MapperException
     */
    public function testConnectColumnNotExists(): void
    {
        $search = new Search(new SQLiteNameTestRecord());
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Unknown relation key *not-known* in mapper for table *x_name_test*');
        $search->exact('not-known', 'empty_value');
    }

    /**
     * @throws MapperException
     */
    protected function dataRefill(): void
    {
        $this->assertTrue($this->database->exec($this->dropTable(), []));
        $this->assertTrue($this->database->exec($this->basicTable(), []));
        $this->assertTrue($this->database->exec($this->fillTable(), []));
        $this->assertEquals(8, $this->database->rowCount());
    }

    protected function dropTable(): string
    {
        return 'DROP TABLE IF EXISTS "x_name_test"';
    }

    protected function basicTable(): string
    {
        return 'CREATE TABLE IF NOT EXISTS "x_name_test" (
  "x_id" INT AUTO_INCREMENT NOT NULL PRIMARY KEY ,
  "x_name" VARCHAR(20) NOT NULL,
  "x_par" INT NULL
)';
    }

    protected function fillTable(): string
    {
        return 'INSERT INTO "x_name_test" ("x_id", "x_name", "x_par") VALUES
( 3, "Vaclav", 4),
( 4, "Jakub", 18),
( 5, "Michal", 4),
( 7, "Vladan", 10),
(10, "Radek", null),
(11, "Vojtech", 4),
(14, "Zbynek", 7),
(18, "Evgenij", null)
';
    }
}


/**
 * Class SQLiteNameTestRecord
 * @property int id
 * @property string name
 * @property int par
 * @property SQLiteNameTestRecord[] pars
 */
class SQLiteNameTestRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('name', IEntryType::TYPE_STRING, 250);
        $this->addEntry('par', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('pars', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper('\StorageTests\Database\DeepQuery\SQLiteNameTestMapper');
    }
}


class SQLiteNameTestMapper extends ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('test_sqlite_local');
        $this->setTable('x_name_test');
        $this->setRelation('id', 'x_id');
        $this->setRelation('name', 'x_name');
        $this->setRelation('par', 'x_par');
        $this->addPrimaryKey('id');
        $this->addForeignKey('pars', '\StorageTests\Database\DeepQuery\SQLiteNameTestRecord', 'par', 'id');
    }
}

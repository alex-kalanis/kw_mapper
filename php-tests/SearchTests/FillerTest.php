<?php

namespace SearchTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Database\ADatabase;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Search\Connector\Database\Filler;
use kalanis\kw_mapper\Search\Connector\Database\RecordsInJoin;
use kalanis\kw_mapper\Storage;
use kalanis\kw_mapper\Storage\Database;


class FillerTest extends CommonTestClass
{
    protected function setUp(): void
    {
        Database\ConfigStorage::getInstance()->addConfig(
            Database\Config::init()->setTarget(
                IDriverSources::TYPE_PDO_MYSQL,
                'testing',
                'localhost',
                3306,
                'kwdeploy',
                'testingpass',
                'kw_deploy'
            )
        );
    }

    /**
     * @throws MapperException
     */
    public function testSimpleFill(): void
    {
        $record = new XRecordChild();
        $record2 = new XRecordParent();
        $lib = new Filler($record);
        $records = [ // you must define all wanted records with their aliases used for join
            (new RecordsInJoin())->setData($record, $record->getMapper()->getAlias(), null, ''), // primary record has its alias as key and must have empty parent
            (new RecordsInJoin())->setData($record2, 'as_is', $record->getMapper()->getAlias(), 'prt'), // other records has aliases defined by their parents or by custom value
        ];
        $lib->initTreeSolver($records);
        // more than once - ignore this one
        $join2 = new Storage\Shared\QueryBuilder\Join();
        $join2->setData(
            'as_is', // alias from join
            'kw_mapper_parent_testing', // parent
            'kmpt_id', // colum in parent
            'kw_mapper_child_testing', // child which want join
            'kmpt_id', // column in child
            'not need here', // for join query itself
            '' // referred in query
        );
        $struct = $lib->getColumns([$this->basicJoin(), $join2]);
        $this->assertEquals([ // tables
            'kw_mapper_child_testing',
            'kw_mapper_child_testing',
            'kw_mapper_child_testing',
            'as_is',
            'as_is',
        ], array_column($struct, 0));
        $this->assertEquals([ // columns
            'kmct_id',
            'kmct_name',
            'kmpt_id',
            'kmpt_id',
            'kmpt_name',
        ], array_column($struct, 1));
        $this->assertEquals([ // aliases
            'kw_mapper_child_testing____kmct_id',
            'kw_mapper_child_testing____kmct_name',
            'kw_mapper_child_testing____kmpt_id',
            'as_is____kmpt_id',
            'as_is____kmpt_name',
        ], array_column($struct, 2));
    }

    /**
     * @throws MapperException
     */
    public function testSimpleParse1(): void
    {
        $record = new XRecordChild();
        $lib = new Filler($record);

        $wantedRecords = [ // you must define all wanted records with their aliases used for join
            (new RecordsInJoin())->setData($record, $record->getMapper()->getAlias(), null, ''), // primary record has its alias as key and must have empty parent
        ];
        $lib->initTreeSolver($wantedRecords);
        $records = $lib->fillResults($this->resultData1());

        // check records - four found
        /** @var XRecordChild[] $records */
        $this->assertEquals(4, count($records));
        $child = reset($records);
        $this->assertEquals('1', $child->id);
        $this->assertEquals('abc', $child->name);
        $child = next($records);
        $this->assertEquals('2', $child->id);
        $this->assertEquals('ghi', $child->name);
        $child = next($records);
        $this->assertEquals('3', $child->id);
        $this->assertEquals('ijk', $child->name);
        $child = next($records);
        $this->assertEquals('4', $child->id);
        $this->assertEquals('lmn', $child->name);
    }

    /**
     * @throws MapperException
     */
    public function testSimpleParse2(): void
    {
        $record = new XRecordChild();
        $lib = new Filler($record);

        $wantedRecords = [ // you must define all wanted records with their aliases used for join
            (new RecordsInJoin())->setData($record, $record->getMapper()->getAlias(), null, ''), // primary record has its alias as key and must have empty parent
        ];
        $lib->initTreeSolver($wantedRecords);
        $records = $lib->fillResults($this->resultData2());

        // check records - four found
        /** @var XRecordChild[] $records */
        $this->assertEquals(4, count($records));
        $child = reset($records);
        $this->assertEquals('1', $child->id);
        $this->assertEquals('abc', $child->name);
        $child = next($records);
        $this->assertEquals('2', $child->id);
        $this->assertEquals('ghi', $child->name);
        $child = next($records);
        $this->assertEquals('3', $child->id);
        $this->assertEquals('ijk', $child->name);
        $child = next($records);
        $this->assertEquals('4', $child->id);
        $this->assertEquals('lmn', $child->name);
    }

    /**
     * @throws MapperException
     */
    public function testChildParse1(): void
    {
        $record = new XRecordChild();
        $record2 = new XRecordParent();
        $lib = new Filler($record);

        $wantedRecords = [ // you must define all wanted records with their aliases used for join
            (new RecordsInJoin())->setData($record, $record->getMapper()->getAlias(), null, ''), // primary record has its alias as key and must have empty parent
            (new RecordsInJoin())->setData($record2, 'as_is', $record->getMapper()->getAlias(), 'prt'), // other records has aliases defined by their parents or by custom value
        ];
        // more than once - ignore this one
        $join2 = new Storage\Shared\QueryBuilder\Join();
        $join2->setData(
            'as_is', // alias from join
            'kw_mapper_parent_testing', // parent
            'kmpt_id', // colum in parent
            'kw_mapper_child_testing', // child which want join
            'kmpt_id', // column in child
            'not need here', // for join query itself
            '' // referred in query
        );

        $lib->initTreeSolver($wantedRecords);
        $records = $lib->fillResults($this->resultData3());

        // check records - four found
        /** @var XRecordChild[] $records */
        $this->assertEquals(4, count($records));
        $child = reset($records);
        $this->assertEquals('1', $child->id);
        $this->assertEquals('abc', $child->name);
        $child = next($records);
        $this->assertEquals('2', $child->id);
        $this->assertEquals('ghi', $child->name);
        $child = next($records);
        $this->assertEquals('3', $child->id);
        $this->assertEquals('ijk', $child->name);
        $child = next($records);
        $this->assertEquals('4', $child->id);
        $this->assertEquals('lmn', $child->name);

        // check parents - only 2 get
        /** @var XRecordParent[] $parents */
        $parents = [];
        foreach ($records as $record) {
            $subs = $record->offsetGet('prt');
            /** @var XRecordParent[] $subs */
            $sub = reset($subs);
            $key = strval($sub->offsetGet('id'));
            if (empty($parents[$key])) {
                $parents[$key] = $sub;
            }
        }
        $this->assertEquals(2, count($parents));

        $parent = reset($parents);
        $this->assertEquals('1', $parent->id);
        $this->assertEquals('def', $parent->name);
        $parent = next($parents);
        $this->assertEquals('2', $parent->id);
        $this->assertEquals('opq', $parent->name);
    }

    /**
     * @throws MapperException
     */
    public function testChildParse2(): void
    {
        $record = new XRecordChild();
        $record2 = new XRecordParent();
        $lib = new Filler($record);

        $wantedRecords = [ // you must define all wanted records with their aliases used for join
            (new RecordsInJoin())->setData($record, $record->getMapper()->getAlias(), null, ''), // primary record has its alias as key and must have empty parent
            (new RecordsInJoin())->setData($record2, 'as_is', $record->getMapper()->getAlias(), 'prt'), // other records has aliases defined by their parents or by custom value
        ];

        $lib->initTreeSolver($wantedRecords);
        $records = $lib->fillResults($this->resultData4());

        // check records
        /** @var XRecordChild[] $records */
        /** @var XRecordChild $child */
        $this->assertEquals(6, count($records));
        $child = reset($records);

        $this->assertEquals('1', $child->id);
        $this->assertEquals('abc', $child->name);
        $this->assertEquals(1, count($child->prt));
        $inner1 = $child->prt;
        $inner1 = reset($inner1);
        $this->assertEquals('1', $inner1->id);
        $this->assertEquals('def', $inner1->name);

        $child = next($records);
        $this->assertEquals('1', $child->id);
        $this->assertEquals('abc', $child->name);
        $this->assertEquals(1, count($child->prt));
        $inner2 = $child->prt;
        $inner2 = reset($inner2);
        $this->assertEquals('2', $inner2->id);
        $this->assertEquals('opq', $inner2->name);

        $child = next($records);
        $this->assertEquals('2', $child->id);
        $this->assertEquals('ghi', $child->name);
        $this->assertEquals(1, count($child->prt));
        $inner3 = $child->prt;
        $inner3 = reset($inner3);
        $this->assertEquals('1', $inner3->id);
        $this->assertEquals('def', $inner3->name);

        $child = next($records);
        $this->assertEquals('2', $child->id);
        $this->assertEquals('ghi', $child->name);
        $this->assertEquals(1, count($child->prt));
        $inner4 = $child->prt;
        $inner4 = reset($inner4);
        $this->assertEquals('2', $inner4->id);
        $this->assertEquals('opq', $inner4->name);

        $child = next($records);
        $this->assertEquals('3', $child->id);
        $this->assertEquals('jkl', $child->name);
        $this->assertEquals(0, count($child->prt));

        $child = next($records);
        $this->assertEquals('4', $child->id);
        $this->assertEquals('mno', $child->name);
        $this->assertEquals(1, count($child->prt));
        $inner5 = $child->prt;
        $inner5 = reset($inner5);
        $this->assertEquals('3', $inner5->id);
        $this->assertEquals('uhb', $inner5->name);

        $this->assertEquals($inner1, $inner3);
        $this->assertEquals($inner2, $inner4);
        $this->assertNotEquals($inner1, $inner5);
        $this->assertNotEquals($inner2, $inner5);
    }

    /**
     * @throws MapperException
     */
    public function testFailedRecordNotExists(): void
    {
        $record = new XRecordChild();
        $record2 = new XRecordParent();
        $lib = new Filler($record);

        $wantedRecords = [ // you must define all wanted records with their aliases used for join
            (new RecordsInJoin())->setData($record, $record->getMapper()->getAlias(), null, ''), // primary record has its alias as key and must have empty parent
            (new RecordsInJoin())->setData($record2, 'not_known', $record->getMapper()->getAlias(), 'prt'), // other records has aliases defined by their parents or by custom value
        ];
        $lib->initTreeSolver($wantedRecords);
        $this->expectException(MapperException::class);
        $lib->fillResults($this->resultData3());
    }

    /**
     * @throws MapperException
     */
    public function testFailedRootRecordNotExists(): void
    {
        $record = new XRecordChild();
        $record2 = new XRecordParent();
        $lib = new Filler($record);

        $wantedRecords = [ // you must define all wanted records with their aliases used for join
            (new RecordsInJoin())->setData($record, $record->getMapper()->getAlias(), 'not_known', ''), // primary record has its alias as key and must have empty parent
            (new RecordsInJoin())->setData($record2, 'as_is', 'not_known', 'prt'), // other records has aliases defined by their parents or by custom value
        ];
        $lib->initTreeSolver($wantedRecords);
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('No root record found.');
        $lib->fillResults($this->resultData3());
    }

    protected function basicJoin(): Storage\Shared\QueryBuilder\Join
    {
        $join = new Storage\Shared\QueryBuilder\Join();
        $join->setData(
            'prt', // alias from join
            'kw_mapper_parent_testing', // parent
            'kmpt_id', // column in parent
            'kw_mapper_child_testing', // child which want join
            'kmpt_id', // column in child
            'not need here', // for join query itself
            'as_is' // referred in query
        );
        return $join;
    }

    /**
     * Result array as it come from database
     * @return string[][]|int[][]
     */
    protected function resultData1(): array
    {
        return [
            [
                'kmct_id' => 1,
                'kmct_name' => 'abc',
                'kmpt_id' => 1,
            ],
            [
                'kmct_id' => 2,
                'kmct_name' => 'ghi',
                'kmpt_id' => 1,
            ],
            [
                'kmct_id' => 3,
                'kmct_name' => 'ijk',
                'kmpt_id' => 1,
            ],
            [
                'kmct_id' => 4,
                'kmct_name' => 'lmn',
                'kmpt_id' => 2,
            ],
        ];
    }

    /**
     * Result array as it come from database
     * @return string[][]|int[][]
     */
    protected function resultData2(): array
    {
        return [
            [
                'kw_mapper_child_testing.kmct_id' => 1,
                'kw_mapper_child_testing.kmct_name' => 'abc',
                'kw_mapper_child_testing.kmpt_id' => 1,
            ],
            [
                'kw_mapper_child_testing.kmct_id' => 2,
                'kw_mapper_child_testing.kmct_name' => 'ghi',
                'kw_mapper_child_testing.kmpt_id' => 1,
            ],
            [
                'kw_mapper_child_testing.kmct_id' => 3,
                'kw_mapper_child_testing.kmct_name' => 'ijk',
                'kw_mapper_child_testing.kmpt_id' => 1,
            ],
            [
                'kw_mapper_child_testing.kmct_id' => 4,
                'kw_mapper_child_testing.kmct_name' => 'lmn',
                'kw_mapper_child_testing.kmpt_id' => 2,
            ],
        ];
    }

    /**
     * Result array as it come from database
     * @return string[][]|int[][]
     */
    protected function resultData3(): array
    {
        return [
            [
                'kw_mapper_child_testing____kmct_id' => 1,
                'kw_mapper_child_testing____kmct_name' => 'abc',
                'kw_mapper_child_testing____kmpt_id' => 1,
                'as_is____kmpt_id' => 1,
                'as_is____kmpt_name' => 'def',
            ],
            [
                'kw_mapper_child_testing____kmct_id' => 2,
                'kw_mapper_child_testing____kmct_name' => 'ghi',
                'kw_mapper_child_testing____kmpt_id' => 1,
                'as_is____kmpt_id' => 1,
                'as_is____kmpt_name' => 'def',
            ],
            [
                'kw_mapper_child_testing____kmct_id' => 3,
                'kw_mapper_child_testing____kmct_name' => 'ijk',
                'kw_mapper_child_testing____kmpt_id' => 1,
                'as_is____kmpt_id' => 1,
                'as_is____kmpt_name' => 'def',
            ],
            [
                'kw_mapper_child_testing____kmct_id' => 4,
                'kw_mapper_child_testing____kmct_name' => 'lmn',
                'kw_mapper_child_testing____kmpt_id' => 2,
                'as_is____kmpt_id' => 2,
                'as_is____kmpt_name' => 'opq',
            ],
        ];
    }

    /**
     * Result array as it come from database
     * @return string[][]|int[][]
     */
    protected function resultData4(): array
    {
        return [
            [ // 4 with cross references
                'kw_mapper_child_testing____kmct_id' => 1,
                'kw_mapper_child_testing____kmct_name' => 'abc',
                'kw_mapper_child_testing____kmpt_id' => 1,
                'as_is____kmpt_id' => 1,
                'as_is____kmpt_name' => 'def',
            ],
            [
                'kw_mapper_child_testing____kmct_id' => 1,
                'kw_mapper_child_testing____kmct_name' => 'abc',
                'kw_mapper_child_testing____kmpt_id' => 2,
                'as_is____kmpt_id' => 2,
                'as_is____kmpt_name' => 'opq',
            ],
            [
                'kw_mapper_child_testing____kmct_id' => 2,
                'kw_mapper_child_testing____kmct_name' => 'ghi',
                'kw_mapper_child_testing____kmpt_id' => 1,
                'as_is____kmpt_id' => 1,
                'as_is____kmpt_name' => 'def',
            ],
            [
                'kw_mapper_child_testing____kmct_id' => 2,
                'kw_mapper_child_testing____kmct_name' => 'ghi',
                'kw_mapper_child_testing____kmpt_id' => 2,
                'as_is____kmpt_id' => 2,
                'as_is____kmpt_name' => 'opq',
            ],
            [ // no child
                'kw_mapper_child_testing____kmct_id' => 3,
                'kw_mapper_child_testing____kmct_name' => 'jkl',
                'kw_mapper_child_testing____kmpt_id' => null,
                'as_is____kmpt_id' => null,
                'as_is____kmpt_name' => null,
            ],
            [ // one separated, no another reference
                'kw_mapper_child_testing____kmct_id' => 4,
                'kw_mapper_child_testing____kmct_name' => 'mno',
                'kw_mapper_child_testing____kmpt_id' => 3,
                'as_is____kmpt_id' => 3,
                'as_is____kmpt_name' => 'uhb',
            ],
        ];
    }
}


/**
 * Class XRecordParent
 * @package SearchTests
 * @property int $id
 * @property string $name
 * @property XRecordChild[] $chld
 */
class XRecordParent extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 512);
        $this->addEntry('name', IEntryType::TYPE_STRING, 512);
        $this->addEntry('chld', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper(XMapperParent::class);
    }
}


/**
 * Class XRecordChild
 * @package SearchTests
 * @property int $id
 * @property string $name
 * @property int $prtId
 * @property XRecordParent[] $prt
 */
class XRecordChild extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 512);
        $this->addEntry('name', IEntryType::TYPE_STRING, 512);
        $this->addEntry('prtId', IEntryType::TYPE_INTEGER, 64); // ID of remote
        $this->addEntry('prt', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper(XMapperChild::class);
    }
}


class XMapperParent extends ADatabase
{
    public function setMap(): void
    {
        $this->setSource('testing');
        $this->setTable('kw_mapper_parent_testing');
        $this->setRelation('id', 'kmpt_id');
        $this->setRelation('name', 'kmpt_name');
        $this->addPrimaryKey('id');
        $this->addForeignKey('chld', XRecordChild::class, 'chldId', 'id');
    }
}


class XMapperChild extends ADatabase
{
    public function setMap(): void
    {
        $this->setSource('testing');
        $this->setTable('kw_mapper_child_testing');
        $this->setRelation('id', 'kmct_id');
        $this->setRelation('name', 'kmct_name');
        $this->setRelation('prtId', 'kmpt_id');
        $this->addPrimaryKey('id');
        $this->addForeignKey('prt', XRecordParent::class, 'prtId', 'id');
    }
}

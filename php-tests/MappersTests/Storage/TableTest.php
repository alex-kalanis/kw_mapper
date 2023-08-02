<?php

namespace MappersTests\Storage;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Storage;
use kalanis\kw_storage\Storage\Key\StaticPrefixKey;


/**
 * Class TableTest
 * @package MappersTests\Storage
 * Need numeric PK and without PK
 * Need some with different entries loaded
 */
class TableTest extends CommonTestClass
{
    public function setUp(): void
    {
        StaticPrefixKey::setPrefix(realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data') . DIRECTORY_SEPARATOR);
    }

    public function tearDown(): void
    {
        $ld = new StaticPrefixKey();
        $path = $ld->fromSharedKey($this->getTestFile());
        if (is_file($path)) {
            @unlink($path);
        }
        StaticPrefixKey::setPrefix('');
    }

    /**
     * @throws MapperException
     */
    public function testMulti(): void
    {
        $data = new \TableRecord();
        $this->assertEquals(5, count($data->loadMultiple()));

        $data->sub = true;
        $this->assertEquals(3, $data->count());
    }

    /**
     * @throws MapperException
     */
    public function testSingle(): void
    {
        $data = new \TableRecord();
        $data->file = 'nonexistent';
        $this->assertFalse($data->load());
        $this->assertEquals(null, $data->title);

        $data->file = 'dummy2.htm';
        $this->assertTrue($data->load());
        $this->assertEquals('ghi', $data->title);
    }

    /**
     * @throws MapperException
     */
    public function testOperationsOnMeta(): void
    {
        $source = $this->initSource();

        $data = new \TableRecord();
        $data->getMapper()->setSource($source);
        $data->getMapper()->orderFromFirst(true);

        // clear insert
        $data->file = 'another';
        $data->title = 'file';
        $data->desc = 'to load';
        $data->order = 11;
        $data->sub = false;
        $this->assertTrue($data->save(true)); // insert with PK - must use force

        // find by PK
        $data = new \TableRecord();
        $this->assertEquals($this->getTestFile(), $data->getMapper()->getSource()); // just check between set

        $data->file = 'another';
        $this->assertTrue($data->load());
        $this->assertEquals('to load', $data->desc);

        // update by PK
        $data = new \TableRecord();
        $data->file = 'another';
        $this->assertTrue($data->load());
        $data->title = 'experiment';
        $this->assertTrue($data->save());

        $data = new \TableRecord();
        $data->file = 'another';
        $this->assertTrue($data->load());
        $this->assertEquals('experiment', $data->title);

        // delete by PK
        $data = new \TableRecord();
        $data->file = 'another';
        $this->assertTrue($data->delete());
    }

    /**
     * @throws MapperException
     */
    public function testOperationsOnPk(): void
    {
        $source = $this->initSource($this->getSourceFileClassic());

        $data = new \TableIdRecord();
        $data->useIdAsMapper();
        $data->getMapper()->setSource($source);
        $data->getMapper()->orderFromFirst(true);

        // clear insert
        $data->file = 'another';
        $data->title = 'file';
        $data->desc = 'to load';
        $data->enabled = false;
        $this->assertTrue($data->save()); // insert without PK into table with PK

        // find by PK
        $data = new \TableIdRecord();
        $data->useIdAsMapper();
        $this->assertEquals($this->getTestFile(), $data->getMapper()->getSource()); // just check between set

        $data->id = 6;
        $this->assertTrue($data->load());
        $this->assertEquals('to load', $data->desc);

        // update by PK
        $data = new \TableIdRecord();
        $data->useIdAsMapper();
        $data->id = 6;
        $this->assertTrue($data->load());
        $data->title = 'experiment';
        $this->assertTrue($data->save());

        $data = new \TableIdRecord();
        $data->useIdAsMapper();
        $data->id = 6;
        $this->assertTrue($data->load());
        $this->assertEquals('experiment', $data->title);

        // delete by PK
        $data = new \TableIdRecord();
        $data->useIdAsMapper();
        $data->id = 6;
        $this->assertTrue($data->delete());
    }

    /**
     * @throws MapperException
     */
    public function testOperationsNoPk(): void
    {
        $source = $this->initSource($this->getSourceFileClassic());

        $data = new \TableIdRecord();
        $data->useNoKeyMapper();
        $data->getMapper()->setSource($source);
        $data->getMapper()->orderFromFirst(false);

        // clear insert
        $data->id = 6;
        $data->file = 'another';
        $data->title = 'file';
        $data->desc = 'to load';
        $data->enabled = false;
        $this->assertTrue($data->save(true)); // insert into table without PK - you must set own id

        // find by PK
        $data = new \TableIdRecord();
        $data->useNoKeyMapper();
        $this->assertEquals($this->getTestFile(), $data->getMapper()->getSource()); // just check between set

        $data->id = 6;
        $this->assertTrue($data->load());
        $this->assertEquals('to load', $data->desc);

        // update by PK
        $data = new \TableIdRecord();
        $data->useNoKeyMapper();
        $data->id = 6;
        $this->assertTrue($data->load());
        $data->title = 'experiment';
        $this->assertTrue($data->save());

        $data = new \TableIdRecord();
        $data->useNoKeyMapper();
        $data->id = 6;
        $this->assertTrue($data->load());
        $this->assertEquals('experiment', $data->title);

        // delete by PK
        $data = new \TableIdRecord();
        $data->useNoKeyMapper();
        $data->id = 6;
        $this->assertTrue($data->delete());
    }

    /**
     * @throws MapperException
     */
    public function testNonExistent(): void
    {
        $source = $this->initSource();

        $data1 = new \TableRecord();
        $data1->getMapper()->setSource($source);

        // clear insert
        $data1->file = 'another';
        $data1->title = 'file';
        $data1->desc = 'to load';
        $data1->order = 11;
        $data1->sub = false;
        $this->assertTrue($data1->save()); // insert with PK - no force, not preset, just insert

        // forced exists insert - false
        $data2 = new \TableRecord();
        $data2->file = 'unknown.htm';
        $data2->title = 'file';
        $data2->desc = 'to load';
        $data2->order = 12;
        $data2->sub = false;
        $this->assertFalse($data2->save(true)); // insert with PK - use force, exists but cannot update

        // update non-existent
        $data3 = new \TableRecord();
        $data3->getEntry('file')->setData('some.htm', true);
        $data3->getEntry('title')->setData('part', true);
        $data3->desc = 'to store';
        $data3->order = 13;
        $data3->sub = false;
        $this->assertFalse($data3->save()); // update with PK - not exists, cannot update

        // update by PK
        $data4 = new \TableRecord();
        $data4->file = 'unknown';
        $this->assertFalse($data4->load());

        // delete by PK
        $data5 = new \TableRecord();
        $data5->file = 'unknown';
        $this->assertFalse($data5->delete());
    }

    /**
     * @throws MapperException
     */
    public function testBefore(): void
    {
        $source = $this->initSource();

        $data1 = new TableBefore();
        $data1->getMapper()->setSource($source);
        $data1->file = 'another';
        $data1->title = 'file';
        $data1->desc = 'to load';
        $data1->order = 21;
        $data1->sub = false;
        $this->assertFalse($data1->save()); // save with failing before

        $data2 = new TableBefore();
        $data2->file = 'dummy2.htm';
        $this->assertFalse($data2->load()); // load with failing before
    }

    /**
     * @throws MapperException
     */
    public function testAfter(): void
    {
        $source = $this->initSource();

        $data1 = new TableAfter();
        $data1->getMapper()->setSource($source);

        $data1->file = 'another';
        $data1->title = 'file';
        $data1->desc = 'to load';
        $data1->order = 31;
        $data1->sub = false;
        $this->assertFalse($data1->save()); // save with failing after

        $data2 = new TableAfter();
        $data2->getMapper()->setSource($source);
        $data2->file = 'dummy2.htm';
        $this->assertFalse($data2->load()); // load with failing after
    }

    protected function initSource(string $source = ''): string
    {
        $ld = new StaticPrefixKey();
        $source = empty($source) ? $this->getSourceFileMeta() : $source ;
        $target = $this->getTestFile();
        copy($ld->fromSharedKey($source), $ld->fromSharedKey($target));
        return $target;
    }

    protected function getSourceFileMeta(): string
    {
        return 'target.meta';
    }

    protected function getSourceFileClassic(): string
    {
        return 'target.data';
    }

    protected function getTestFile(): string
    {
        return 'tableTest.txt';
    }
}


class TableBefore extends \TableRecord
{
    protected function addEntries(): void
    {
        parent::addEntries();
        $this->setMapper(TableBeforeMapper::class);
    }
}


class TableBeforeMapper extends \TableMapper
{
    protected function beforeLoad(ARecord $record): bool
    {
        return false;
    }

    protected function beforeSave(ARecord $record): bool
    {
        return false;
    }
}


class TableAfter extends \TableRecord
{
    protected function addEntries(): void
    {
        parent::addEntries();
        $this->setMapper(TableAfterMapper::class);
    }
}


class TableAfterMapper extends \TableMapper
{
    protected function afterLoad(ARecord $record): bool
    {
        return false;
    }

    protected function afterSave(ARecord $record): bool
    {
        return false;
    }
}

<?php

namespace MappersTests\File;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Storage;


/**
 * Class TableTest
 * @package MappersTests\File
 * Need numeric PK and without PK
 * Need some with different entries loaded
 */
class TableTest extends CommonTestClass
{
    public function tearDown(): void
    {
        $path = $this->getTestFile();
        if (is_file($path)) {
            @unlink($path);
        }
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
        $data = new \TableRecord();
        $this->initSource($data);
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
        $data = new \TableIdRecord();
        $data->useIdAsMapper();
        $this->initSource($data, $this->getSourceFileClassic());
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
        $data = new \TableIdRecord();
        $data->useNoKeyMapper();
        $this->initSource($data, $this->getSourceFileClassic());
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
        $data = new \TableRecord();
        $this->initSource($data);

        // clear insert
        $data->file = 'another';
        $data->title = 'file';
        $data->desc = 'to load';
        $data->order = 11;
        $data->sub = false;
        $this->assertFalse($data->save()); // insert with PK - no force, not found, cannot update

        // forced exists insert - false
        $data = new \TableRecord();
        $data->file = 'unknown.htm';
        $data->title = 'file';
        $data->desc = 'to load';
        $data->order = 11;
        $data->sub = false;
        $this->assertFalse($data->save(true)); // insert with PK - use force, exists but cannot update

        // update by PK
        $data = new \TableRecord();
        $data->file = 'unknown';
        $this->assertFalse($data->load());

        // delete by PK
        $data = new \TableRecord();
        $data->file = 'unknown';
        $this->assertFalse($data->delete());
    }

    /**
     * @throws MapperException
     */
    public function testBefore(): void
    {
        $data = new TableBefore();
        $this->initSource($data);

        $data->file = 'another';
        $data->title = 'file';
        $data->desc = 'to load';
        $data->order = 11;
        $data->sub = false;
        $this->assertFalse($data->save()); // save with failing before

        $data = new TableBefore();
        $data->file = 'dummy2.htm';
        $this->assertFalse($data->load()); // load with failing before
    }

    /**
     * @throws MapperException
     */
    public function testAfter(): void
    {
        $data = new TableAfter();
        $this->initSource($data);

        $data->file = 'another';
        $data->title = 'file';
        $data->desc = 'to load';
        $data->order = 11;
        $data->sub = false;
        $this->assertFalse($data->save()); // save with failing after

        $data = new TableAfter();
        $data->file = 'dummy2.htm';
        $this->assertFalse($data->load()); // load with failing after
    }

    protected function initSource(ARecord $record, string $source = ''): void
    {
        $source = empty($source) ? $this->getSourceFileMeta() : $source ;
        $target = $this->getTestFile();
        copy($source, $target);
        $record->getMapper()->setSource($target);
    }

    protected function getSourceFileMeta(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'target.meta';
    }

    protected function getSourceFileClassic(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'target.data';
    }

    protected function getTestFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'tableTest.txt';
    }
}


class TableBefore extends \TableRecord
{
    protected function addEntries(): void
    {
        parent::addEntries();
        $this->setMapper('\MappersTests\File\TableBeforeMapper');
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
        $this->setMapper('\MappersTests\File\TableAfterMapper');
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

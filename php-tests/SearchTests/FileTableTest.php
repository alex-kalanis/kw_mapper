<?php

namespace SearchTests;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Search\Search;
use kalanis\kw_mapper\Storage;


class FileTableTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testSimpleSearch(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->orderBy('title');
        $this->assertEquals(5, $lib->getCount());
        $lib->offset(1);
        $lib->limit(3);
        $lib->useAnd(); // just call
        $lib->useOr(); // just call
        $this->assertEquals(5, $lib->getCount());
        $this->assertEquals(3, count($lib->getResults()));
    }

    /**
     * @throws MapperException
     */
    public function testSearchFailPropertyDefine(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();

        $lib = new Search($record);
        $this->expectException(MapperException::class);
        $lib->like('.file', 'mm');
    }

    /**
     * @throws MapperException
     */
    public function testSearchLike(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();

        $lib = new Search($record);
        $lib->like('file', 'dummy');
        $this->assertEquals(4, $lib->getCount());
        $this->assertNotEmpty($lib->getResults());

        $lib = new Search($record);
        $lib->like('file.file', 'mm');
        $this->assertEquals(4, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchNotLike(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();

        $lib = new Search($record);
        $lib->notLike('file.file', 'now');
        $this->assertEquals(4, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchExact(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->exact('enabled', true);
        $this->assertEquals(3, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchNotExact(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->notExact('enabled', true);
        $this->assertEquals(2, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchFrom(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->from('id', 2, true);
        $this->assertEquals(4, $lib->getCount());

        $lib = new Search($record);
        $lib->from('id', 2, false);
        $this->assertEquals(3, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchTo(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->to('id', 3, false);
        $this->assertEquals(2, $lib->getCount());

        $lib = new Search($record);
        $lib->to('id', 3, true);
        $this->assertEquals(3, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testRegex(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->regexp('file', '#dummy(\d+)#');
        $this->assertEquals(4, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchBetween(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->between('id', 2, 5);
        $this->assertEquals(4, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchNull(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->null('title');
        $this->assertEquals(0, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchNotNull(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->notNull('title');
        $this->assertEquals(5, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchIn(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->in('desc', ['jkl', 'pqr', 'z12']);
        $this->assertEquals(2, $lib->getCount());
    }

    /**
     * @throws MapperException
     */
    public function testSearchNotIn(): void
    {
        $record = new \TableIdRecord();
        $record->useIdAsMapper();
        $lib = new Search($record);
        $lib->notIn('desc', ['jkl', 'pqr', 'z12']);
        $this->assertEquals(3, $lib->getCount());
    }
}

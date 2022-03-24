<?php

namespace SearchTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Search\Connector\Factory;
use kalanis\kw_mapper\Storage\Database\Config;
use kalanis\kw_mapper\Storage\Database\ConfigStorage;


class FactoryTest extends CommonTestClass
{
    protected function setUp(): void
    {
        $conf = new Config();
        $conf->setTarget(
            IDriverSources::TYPE_PDO_MYSQL,
            'dummy',
            ':--memory--:',
            7777,
            'foo',
            'bar',
            'baz'
        );
        $storage = ConfigStorage::getInstance();
        $storage->addConfig($conf);
    }

    /**
     * @throws MapperException
     */
    public function testDatabase(): void
    {
        $record = new \XSimpleRecord();
        $record->useDatabase();
        $lib = Factory::getInstance();
        $conn = $lib->getConnector($record);
        $this->assertInstanceOf('\kalanis\kw_mapper\Search\Connector\Database', $conn);
    }

    /**
     * @throws MapperException
     */
    public function testFile(): void
    {
        $record = new \XSimpleRecord();
        $record->useFile();
        $lib = Factory::getInstance();
        $conn = $lib->getConnector($record);
        $this->assertInstanceOf('\kalanis\kw_mapper\Search\Connector\FileTable', $conn);
    }

    /**
     * @throws MapperException
     */
    public function testRecords(): void
    {
        $record1 = new \XSimpleRecord();
        $record1->useMock();
        $record1->id = 1;
        $record1->title = 'abc';

        $record2 = new \XSimpleRecord();
        $record2->useMock();
        $record2->id = 2;
        $record2->title = 'def';

        $record3 = new \XSimpleRecord();
        $record3->useMock();
        $record3->id = 3;
        $record3->title = 'ghi';

        $record4 = new \XSimpleRecord();
        $record4->useMock();
        $record4->id = 4;
        $record4->title = 'jkl';

        $lib = Factory::getInstance();
        $conn = $lib->getConnector($record1, [$record1, $record2, $record3, $record4]);
        $this->assertInstanceOf('\kalanis\kw_mapper\Search\Connector\Records', $conn);
    }

    /**
     * @throws MapperException
     */
    public function testBadlyDefined(): void
    {
        $record = new \XSimpleRecord();
        $record->useMock();

        $lib = Factory::getInstance();
        $this->expectException(MapperException::class);
        $lib->getConnector($record);
    }
}

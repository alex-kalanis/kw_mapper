<?php

namespace SearchTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Search\Connector;
use kalanis\kw_mapper\Storage\Database\Config;
use kalanis\kw_mapper\Storage\Database\ConfigStorage;
use kalanis\kw_storage\Storage\Key\StaticPrefixKey;


class FactoryTest extends CommonTestClass
{
    protected function setUp(): void
    {
        // DB
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

        // file
        StaticPrefixKey::setPrefix(realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'target') . DIRECTORY_SEPARATOR);
        $pt = (new StaticPrefixKey())->fromSharedKey($this->mockFile());
        if (is_file($pt)) {
            chmod($pt, 0555);
            unlink($pt);
        }

        parent::setUp();
    }

    public function tearDown(): void
    {
        $pt = (new StaticPrefixKey())->fromSharedKey($this->mockFile());
        if (is_file($pt)) {
            chmod($pt, 0555);
            unlink($pt);
        }
        parent::tearDown();
        StaticPrefixKey::setPrefix('');
    }

    protected function mockFile(): string
    {
        return 'testing_one.txt';
    }

    /**
     * @throws MapperException
     */
    public function testDatabase(): void
    {
        $record = new \XSimpleRecord();
        $record->useDatabase();
        $lib = Connector\Factory::getInstance();
        $conn = $lib->getConnector($record);
        $this->assertInstanceOf(Connector\Database::class, $conn);
    }

    /**
     * @throws MapperException
     */
    public function testStorage(): void
    {
        $record = new \XSimpleRecord();
        $record->useStorage();
        $lib = Connector\Factory::getInstance();
        $conn = $lib->getConnector($record);
        $this->assertInstanceOf(Connector\FileTable::class, $conn);
    }

    /**
     * @throws MapperException
     */
    public function testFile(): void
    {
        $record = new \XSimpleRecord();
        $record->useFile();
        $lib = Connector\Factory::getInstance();
        $conn = $lib->getConnector($record);
        $this->assertInstanceOf(Connector\FileTable::class, $conn);
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

        $lib = Connector\Factory::getInstance();
        $conn = $lib->getConnector($record1, [$record1, $record2, $record3, $record4]);
        $this->assertInstanceOf(Connector\Records::class, $conn);
    }

    /**
     * @throws MapperException
     */
    public function testBadlyDefined(): void
    {
        $record = new \XSimpleRecord();
        $record->useMock();

        $lib = Connector\Factory::getInstance();
        $this->expectException(MapperException::class);
        $lib->getConnector($record);
    }
}

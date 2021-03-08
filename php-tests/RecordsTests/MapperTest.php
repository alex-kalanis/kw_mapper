<?php

namespace RecordsTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\ASimpleRecord;


class MapperTest extends CommonTestClass
{
    public function setUp(): void
    {
        if (is_file($this->mockFile())) {
            chmod($this->mockFile(), 0555);
            unlink($this->mockFile());
        }

        parent::setUp();
    }

    public function tearDown(): void
    {
        if (is_file($this->mockFile())) {
            chmod($this->mockFile(), 0555);
            unlink($this->mockFile());
        }
        parent::tearDown();
    }

    public function testFactory()
    {
        $data = new Mappers\Factory();
        $instance = $data->getInstance('\kalanis\kw_mapper\Mappers\File\PageContent');
        $this->assertInstanceOf('\kalanis\kw_mapper\Mappers\AMapper', $instance);
    }

    public function testFactoryFail1()
    {
        $data = new Mappers\Factory();
        $this->expectException(MapperException::class);
        $data->getInstance('no class');
    }

    public function testFactoryFail2()
    {
        $data = new Mappers\Factory();
        $this->expectException(MapperException::class);
        $data->getInstance('\stdClass');
    }

    public function testSimple()
    {
        $data = new RecordForMapper();
        $data->setExternalEntries();
        $data->path = $this->mockFile();
        $data->content = 'yxcvbnmasdfghjklqwertzuiop';
        $this->assertTrue($data->save());
        $this->assertTrue($data->load());
        $this->assertTrue($data->getEntry('content')->isFromStorage());
        $multi = $data->loadMultiple();
        $this->assertEquals(1, count($multi));
    }

    public function testUnavailable()
    {
        $data = new RecordForMapper();
        $this->expectException(MapperException::class);
        $data->load();
    }

    protected function mockFile(): string
    {
        return implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'data', 'testing_one.txt']);
    }

    public function testNoBefore()
    {
        $data = new RecordForMapper();
        $data->setExternalEntries();
        $data->path = 'qwertzuiop';
        $data->content = 'asdfghjkl';

        $mapper = new NoBeforeMapper();
        $this->assertFalse($mapper->insert($data));
        $this->assertFalse($mapper->updateSome($data));
        $this->assertFalse($mapper->save($data));
        $this->assertFalse($mapper->load($data));
        $this->assertFalse($mapper->delete($data));
    }

    public function testNoDuring()
    {
        $data = new RecordForMapper();
        $data->setExternalEntries();
        $data->path = 'qwertzuiop';
        $data->content = 'asdfghjkl';

        $mapper = new NoDuringMapper();
        $this->assertFalse($mapper->insert($data));
        $this->assertFalse($mapper->updateSome($data));
        $this->assertFalse($mapper->save($data));
        $this->assertFalse($mapper->load($data));
        $this->assertFalse($mapper->delete($data));
    }

    public function testNoAfter()
    {
        $data = new RecordForMapper();
        $data->setExternalEntries();
        $data->path = 'qwertzuiop';
        $data->content = 'asdfghjkl';

        $mapper = new NoAfterMapper();
        $this->assertFalse($mapper->insert($data));
        $this->assertFalse($mapper->updateSome($data));
        $this->assertFalse($mapper->save($data));
        $this->assertFalse($mapper->load($data));
        $this->assertFalse($mapper->delete($data));
    }
}


/**
 * Class RecordForMapper
 * @package RecordsTests
 * @property string path
 * @property string content
 */
class RecordForMapper extends ASimpleRecord
{
    protected function addEntries(): void
    {}

    public function setExternalEntries(): void
    {
        $this->addEntry('path', IEntryType::TYPE_STRING, 512);
        $this->addEntry('content', IEntryType::TYPE_STRING, PHP_INT_MAX);
        $this->setMapper('\kalanis\kw_mapper\Mappers\File\PageContent');
    }
}


class NoBeforeMapper extends Mappers\AMapper
{
    protected function setMap(): void
    {
        $this->setRelation('path', 0);
        $this->addPrimaryKey('path');
    }

    public function getAlias(): string
    {
        return 'dummy';
    }

    public function updateSome(ARecord $record): bool
    {
        return $this->update($record);
    }

    public function countRecord(ARecord $record): int
    {
        return 0;
    }

    public function loadMultiple(ARecord $record): array
    {
        return [];
    }

    protected function insertRecord(ARecord $record): bool
    {
        return true;
    }

    protected function updateRecord(ARecord $record): bool
    {
        return true;
    }

    protected function loadRecord(ARecord $record): bool
    {
        return true;
    }

    protected function deleteRecord(ARecord $record): bool
    {
        return true;
    }

    protected function beforeSave(ARecord $record): bool
    {
        return false;
    }

    protected function beforeDelete(ARecord $record): bool
    {
        return false;
    }

    protected function beforeUpdate(ARecord $record): bool
    {
        return false;
    }

    protected function beforeInsert(ARecord $record): bool
    {
        return false;
    }

    protected function beforeLoad(ARecord $record): bool
    {
        return false;
    }
}


class NoDuringMapper extends Mappers\AMapper
{
    protected function setMap(): void
    {
        $this->setRelation('path', 0);
        $this->addPrimaryKey('path');
    }

    public function getAlias(): string
    {
        return 'dummy';
    }

    public function updateSome(ARecord $record): bool
    {
        return $this->update($record);
    }

    public function countRecord(ARecord $record): int
    {
        return 1;
    }

    public function loadMultiple(ARecord $record): array
    {
        return [];
    }

    protected function insertRecord(ARecord $record): bool
    {
        return false;
    }

    protected function updateRecord(ARecord $record): bool
    {
        return false;
    }

    protected function loadRecord(ARecord $record): bool
    {
        return false;
    }

    protected function deleteRecord(ARecord $record): bool
    {
        return false;
    }
}


class NoAfterMapper extends Mappers\AMapper
{
    protected function setMap(): void
    {
        $this->setRelation('path', 0);
        $this->addPrimaryKey('path');
    }

    public function getAlias(): string
    {
        return 'dummy';
    }

    public function updateSome(ARecord $record): bool
    {
        return $this->update($record);
    }

    public function countRecord(ARecord $record): int
    {
        return 0;
    }

    public function loadMultiple(ARecord $record): array
    {
        return [];
    }

    protected function insertRecord(ARecord $record): bool
    {
        return true;
    }

    protected function updateRecord(ARecord $record): bool
    {
        return true;
    }

    protected function loadRecord(ARecord $record): bool
    {
        return true;
    }

    protected function deleteRecord(ARecord $record): bool
    {
        return true;
    }

    protected function afterSave(ARecord $record): bool
    {
        return false;
    }

    protected function afterDelete(ARecord $record): bool
    {
        return false;
    }

    protected function afterUpdate(ARecord $record): bool
    {
        return false;
    }

    protected function afterInsert(ARecord $record): bool
    {
        return false;
    }

    protected function afterLoad(ARecord $record): bool
    {
        return false;
    }
}

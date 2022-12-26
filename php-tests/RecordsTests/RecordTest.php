<?php

namespace RecordsTests;


use CommonTestClass;
use kalanis\kw_mapper\Adapters;
use kalanis\kw_mapper\Interfaces;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records;
use kalanis\kw_mapper\Storage\Shared\FormatFiles;


class RecordTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testSimple(): void
    {
        $data = new UserSimpleRecord();
        $this->assertEmpty($data['id']);
        $this->assertEmpty($data['name']);
        $this->assertEmpty($data['password']);
        $this->assertEmpty($data['enabled']);
        $this->assertEmpty($data['details']);
        $this->assertNotEmpty($data->getMapper());
        $this->assertInstanceOf(Mappers\AMapper::class, $data->getMapper());

        $data['id'] = '999';
        $data['name'] = 321654897;
        $data['password'] = 'lkjhgfdsa';
        $data['enabled'] = true;
        $data['enabled'] = '1';
        $data['details'] = ['auth' => 'ldap', 'rights' => 'limited'];

        $this->assertEquals('999', $data['id']);
        $this->assertEquals(321654897, $data['name']);
        $this->assertEquals('lkjhgfdsa', $data['password']);
        $this->assertEquals('ldap', $data['details']['auth']);

        $data2 = clone $data;
        $data2->name = 'zgvtfcrdxesy';
        $data2->password = 'mnbvcxy';
        $this->assertEquals('zgvtfcrdxesy', $data2['name']);
        $this->assertEquals('mnbvcxy', $data2['password']);
        $this->assertNotEquals('zgvtfcrdxesy', $data['name']);
        $this->assertNotEquals('mnbvcxy', $data['password']);
        $this->assertEquals(321654897, $data->name);
        $this->assertEquals('lkjhgfdsa', $data->password);

        $objectEntry = $data->getEntry('details');
        $this->assertEquals(Interfaces\IEntryType::TYPE_OBJECT, $objectEntry->getType());
        $this->assertInstanceOf(Interfaces\ICanFill::class, $objectEntry->getData());

        foreach ($data as $key => $entry) {
            $this->assertNotEmpty($key);
            $this->assertNotEmpty($entry);
            $this->assertInstanceOf(Records\Entry::class, $data->getEntry($key));
        }

        $data->details = 'another piece';
        $this->assertEquals('another piece', $data->details);

        $data->loadWithData(['name' => 'okmijn', 'id' => '555', 'unset' => 'asdfghj']);
        $this->assertEquals('okmijn', $data->name);
        $this->assertEquals('555', $data->id);
    }

    /**
     * @throws MapperException
     */
    public function testStrict(): void
    {
        $data = new UserStrictRecord();
        $this->assertEmpty($data['id']);
        $this->assertEmpty($data['name']);
        $this->assertEmpty($data['password']);
        $this->assertEmpty($data['enabled']);
        $this->assertEmpty($data['details']);

        $data['id'] = 999;
        $data['name'] = 'plokmijnuhb';
        $data['password'] = 'lkjhgfdsa';
        $data['enabled'] = true;
        $data['details'] = 'simply';
        $data['details'] = ['auth' => 'ldap', 'rights' => 'limited'];

        $this->assertEquals(999, $data['id']);
        $this->assertEquals('plokmijnuhb', $data['name']);
        $this->assertEquals('lkjhgfdsa', $data['password']);
        $this->assertEquals('ldap', $data['details']['auth']);
    }

    /**
     * @throws MapperException
     */
    public function testCannotAddLater(): void
    {
        $data = new UserStrictRecord();
        $this->expectException(MapperException::class);
        $data['expect'] = 'nothing';
    }

    /**
     * @throws MapperException
     */
    public function testCannotRemove(): void
    {
        $data = new UserStrictRecord();
        $this->expectException(MapperException::class);
        unset($data['password']);
    }

    /**
     * @throws MapperException
     */
    public function testLimitBoolType(): void
    {
        $data1 = new UserSimpleRecord();
        $data1->enabled = null;
        $data1->enabled = 'yes';

        $data2 = new UserStrictRecord();
        $data2->enabled = null;
        $this->expectException(MapperException::class);
        $data2->enabled = 'yes';
    }

    /**
     * @throws MapperException
     */
    public function testLimitIntType(): void
    {
        $data1 = new UserSimpleRecord();
        $data1['id'] = null;
        $data1['id'] = 'yes';

        $data2 = new UserStrictRecord();
        $data2['id'] = null;
        $this->expectException(MapperException::class);
        $data2['id'] = 'yes';
    }

    /**
     * @throws MapperException
     */
    public function testLimitIntSize(): void
    {
        $data1 = new UserSimpleRecord();
        $data1['id'] = 8888;
        $data1['id'] = 8889;

        $data2 = new UserStrictRecord();
        $data2['id'] = 8888;
        $this->expectException(MapperException::class);
        $data2['id'] = 8889;
    }

    /**
     * @throws MapperException
     */
    public function testLimitStringType(): void
    {
        $data1 = new UserSimpleRecord();
        $data1['password'] = null;
        $data1['password'] = new \stdClass();

        $data2 = new UserStrictRecord();
        $data2['password'] = null;
        $this->expectException(MapperException::class);
        $data2['password'] = new \stdClass();
    }

    /**
     * @throws MapperException
     */
    public function testLimitStringSize(): void
    {
        $data1 = new UserSimpleRecord();
        $data1['password'] = 'poiuztrelkjhgfds';
        $data1['password'] = 'poiuztrelkjhgfdsa';

        $data2 = new UserStrictRecord();
        $data2['password'] = 'poiuztrelkjhgfds';
        $this->expectException(MapperException::class);
        $data2['password'] = 'poiuztrelkjhgfdsa';
    }

    /**
     * @throws MapperException
     */
    public function testInvalidLimit(): void
    {
        $this->expectException(MapperException::class);
        new FailedUserRecord1();
    }

    /**
     * @throws MapperException
     */
    public function testInvalidSize(): void
    {
        $this->expectException(MapperException::class);
        new FailedUserRecord2();
    }

    /**
     * @throws MapperException
     */
    public function testInvalidObject(): void
    {
        $this->expectException(MapperException::class);
        new FailedUserRecord3();
    }

    /**
     * @throws MapperException
     */
    public function testInvalidObjectDef(): void
    {
        $this->expectException(MapperException::class);
        new FailedUserRecord4();
    }

    /**
     * @throws MapperException
     */
    public function testInvalidPreset(): void
    {
        $data = new FailedUserRecord5();
        $data->status = null;
        $data->status = 'fail';
        $this->expectException(MapperException::class);
        $data->status = 'not-set';
    }

    /**
     * @throws MapperException
     */
    public function testInsertInvalidInputArray1(): void
    {
        $data = new FailedUserRecord6();
        $this->expectException(MapperException::class);
        $data->others = 'okmijn';
    }

    /**
     * @throws MapperException
     */
    public function testInsertInvalidInputArray2(): void
    {
        $data = new FailedUserRecord6();
        $data->others = [new UserStrictRecord(), new UserSimpleRecord()];
        $this->expectException(MapperException::class);
        $data->others = ['okmijn'];
    }

    /**
     * @throws MapperException
     */
    public function testDataExchange(): void
    {
        $data = new UserSimpleRecord();
        $data['id'] = '999';
        $data['name'] = 321654897;
        $data['password'] = 'lkjhgfdsa';
        $data['enabled'] = true;

        $ex = new Adapters\DataExchange($data);
        $ex->addExclude('password');
        $this->assertEquals(1, $ex->import(['id' => 888, 'password' => 'mnbvcxy']));
        $ex->clearExclude();
        $pack = $ex->export();

        $this->assertNotEmpty($ex->getRecord());
        $this->assertEquals(888, $pack['id']);
        $this->assertEquals(321654897, $pack['name']);
        $this->assertEquals('lkjhgfdsa', $pack['password']);
        $this->assertEquals(true, $pack['enabled']);
    }
}


/**
 * Class UserStrictRecord
 * @package RecordsTests
 * @property int $id
 * @property string $name
 * @property string $password
 * @property bool $enabled
 * @property Adapters\MappedStdClass $details
 */
class UserStrictRecord extends Records\AStrictRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', Interfaces\IEntryType::TYPE_INTEGER, 8888); // max size of inner number is 8888
        $this->addEntry('name', Interfaces\IEntryType::TYPE_STRING, 128);
        $this->addEntry('password', Interfaces\IEntryType::TYPE_STRING, 16); // max length of string is 16 chars
        $this->addEntry('status', Interfaces\IEntryType::TYPE_SET, ['ok', 'fail', 'error']);
        $this->addEntry('enabled', Interfaces\IEntryType::TYPE_BOOLEAN);
        $this->addEntry('others', Interfaces\IEntryType::TYPE_ARRAY);
        $this->addEntry('details', Interfaces\IEntryType::TYPE_OBJECT, Adapters\MappedStdClass::class);
        $this->setMapper(UserFileMapper::class);
    }
}


/**
 * Class UserSimpleRecord
 * @package RecordsTests
 * @property int $id
 * @property string $name
 * @property string $password
 * @property bool $enabled
 * @property Adapters\MappedStdClass $details
 */
class UserSimpleRecord extends Records\ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', Interfaces\IEntryType::TYPE_INTEGER, 8888); // max size of inner number is 8888
        $this->addEntry('name', Interfaces\IEntryType::TYPE_STRING, 128);
        $this->addEntry('password', Interfaces\IEntryType::TYPE_STRING, 16); // max length of string is 16 chars
        $this->addEntry('enabled', Interfaces\IEntryType::TYPE_BOOLEAN);
        $this->addEntry('details', Interfaces\IEntryType::TYPE_OBJECT, Adapters\MappedStdClass::class);
        $this->setMapper(UserFileMapper::class);
    }
}


class FailedUserRecord1 extends Records\ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', 777, '456');
    }
}


class FailedUserRecord2 extends Records\ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', Interfaces\IEntryType::TYPE_INTEGER, 'asdf');
    }
}


class FailedUserRecord3 extends Records\ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('details', Interfaces\IEntryType::TYPE_OBJECT, new \stdClass());
    }
}


class FailedUserRecord4 extends Records\ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('details', Interfaces\IEntryType::TYPE_OBJECT, \stdClass::class);
    }
}


class FailedUserRecord5 extends Records\AStrictRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('status', Interfaces\IEntryType::TYPE_SET, ['ok', 'fail', 'error']);
    }
}


class FailedUserRecord6 extends Records\AStrictRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('others', Interfaces\IEntryType::TYPE_ARRAY);
    }
}


class UserFileMapper extends Mappers\Storage\ATable
{
    protected function setMap(): void
    {
        $this->setSource(__DIR__ . DIRECTORY_SEPARATOR . 'users.txt');
        $this->setFormat(FormatFiles\SeparatedElements::class);
        $this->setRelation('id', 0);
        $this->setRelation('name', 1);
        $this->setRelation('password', 2);
        $this->setRelation('enabled', 3);
        $this->setRelation('details', 4);
        $this->addPrimaryKey('id');
    }
}

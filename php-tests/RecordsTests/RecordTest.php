<?php

namespace RecordsTests;


use CommonTestClass;
use kalanis\kw_mapper\Adapters\DataExchange;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records\AStrictRecord;
use kalanis\kw_mapper\Records\ASimpleRecord;


class RecordTest extends CommonTestClass
{
    public function testSimple()
    {
        $data = new UserSimpleRecord();
        $this->assertEmpty($data['id']);
        $this->assertEmpty($data['name']);
        $this->assertEmpty($data['password']);
        $this->assertEmpty($data['enabled']);
        $this->assertEmpty($data['details']);

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
        $this->assertEquals(IEntryType::TYPE_OBJECT, $objectEntry->getType());
        $this->assertInstanceOf('\kalanis\kw_mapper\Interfaces\ICanFill', $objectEntry->getData());

        foreach ($data as $key => $entry) {
            $this->assertNotEmpty($key);
            $this->assertNotEmpty($entry);
            $this->assertInstanceOf('\kalanis\kw_mapper\Records\Entry', $data->getEntry($key));
        }

        $data->details = 'another piece';
        $this->assertEquals('another piece', $data->details);

        $data->loadWithData(['name' => 'okmijn', 'id' => '555', 'unset' => 'asdfghj']);
        $this->assertEquals('okmijn', $data->name);
        $this->assertEquals('555', $data->id);
    }

    public function testStrict()
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
        $data['details'] = ['auth' => 'ldap', 'rights' => 'limited'];

        $this->assertEquals(999, $data['id']);
        $this->assertEquals('plokmijnuhb', $data['name']);
        $this->assertEquals('lkjhgfdsa', $data['password']);
        $this->assertEquals('ldap', $data['details']['auth']);
    }

    public function testCannotAddLater()
    {
        $data = new UserStrictRecord();
        $this->expectException(MapperException::class);
        $data['expect'] = 'nothing';
    }

    public function testCannotRemove()
    {
        $data = new UserStrictRecord();
        $this->expectException(MapperException::class);
        unset($data['password']);
    }

    public function testLimitBoolType()
    {
        $data1 = new UserSimpleRecord();
        $data1->enabled = null;
        $data1->enabled = 'yes';

        $data2 = new UserStrictRecord();
        $data2->enabled = null;
        $this->expectException(MapperException::class);
        $data2->enabled = 'yes';
    }

    public function testLimitIntType()
    {
        $data1 = new UserSimpleRecord();
        $data1['id'] = null;
        $data1['id'] = 'yes';

        $data2 = new UserStrictRecord();
        $data2['id'] = null;
        $this->expectException(MapperException::class);
        $data2['id'] = 'yes';
    }

    public function testLimitIntSize()
    {
        $data1 = new UserSimpleRecord();
        $data1['id'] = 8888;
        $data1['id'] = 8889;

        $data2 = new UserStrictRecord();
        $data2['id'] = 8888;
        $this->expectException(MapperException::class);
        $data2['id'] = 8889;
    }

    public function testLimitStringType()
    {
        $data1 = new UserSimpleRecord();
        $data1['password'] = null;
        $data1['password'] = new \stdClass();

        $data2 = new UserStrictRecord();
        $data2['password'] = null;
        $this->expectException(MapperException::class);
        $data2['password'] = new \stdClass();
    }

    public function testLimitStringSize()
    {
        $data1 = new UserSimpleRecord();
        $data1['password'] = 'poiuztrelkjhgfds';
        $data1['password'] = 'poiuztrelkjhgfdsa';

        $data2 = new UserStrictRecord();
        $data2['password'] = 'poiuztrelkjhgfds';
        $this->expectException(MapperException::class);
        $data2['password'] = 'poiuztrelkjhgfdsa';
    }

    public function testInvalidLimit()
    {
        $this->expectException(MapperException::class);
        new FailedUserRecord1();
    }

    public function testInvalidSize()
    {
        $this->expectException(MapperException::class);
        new FailedUserRecord2();
    }

    public function testInvalidObject()
    {
        $this->expectException(MapperException::class);
        new FailedUserRecord3();
    }

    public function testInvalidObjectDef()
    {
        $this->expectException(MapperException::class);
        new FailedUserRecord4();
    }

    public function testDataExchange()
    {
        $data = new UserSimpleRecord();
        $data['id'] = '999';
        $data['name'] = 321654897;
        $data['password'] = 'lkjhgfdsa';
        $data['enabled'] = true;

        $ex = new DataExchange($data);
        $ex->addExclude('password');
        $ex->import(['id' => 888, 'password' => 'mnbvcxy']);
        $ex->clearExclude();
        $pack = $ex->export();

        $this->assertEquals(888, $pack['id']);
        $this->assertEquals(321654897, $pack['name']);
        $this->assertEquals('lkjhgfdsa', $pack['password']);
        $this->assertEquals(true, $pack['enabled']);
    }
}


/**
 * Class UserStrictRecord
 * @package RecordsTests
 * @property int id
 * @property string name
 * @property string password
 * @property bool enabled
 * @property \kalanis\kw_mapper\Adapters\MappedStdClass details
 */
class UserStrictRecord extends AStrictRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 8888); // max size of inner number is 8888
        $this->addEntry('name', IEntryType::TYPE_STRING, 128);
        $this->addEntry('password', IEntryType::TYPE_STRING, 16); // max length of string is 16 chars
        $this->addEntry('enabled', IEntryType::TYPE_BOOLEAN);
        $this->addEntry('details', IEntryType::TYPE_OBJECT, '\kalanis\kw_mapper\Adapters\MappedStdClass');
        $this->setMapper('\RecordsTests\UserFileMapper');
    }
}


/**
 * Class UserSimpleRecord
 * @package RecordsTests
 * @property int id
 * @property string name
 * @property string password
 * @property bool enabled
 * @property \kalanis\kw_mapper\Adapters\MappedStdClass details
 */
class UserSimpleRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 8888); // max size of inner number is 8888
        $this->addEntry('name', IEntryType::TYPE_STRING, 128);
        $this->addEntry('password', IEntryType::TYPE_STRING, 16); // max length of string is 16 chars
        $this->addEntry('enabled', IEntryType::TYPE_BOOLEAN);
        $this->addEntry('details', IEntryType::TYPE_OBJECT, '\kalanis\kw_mapper\Adapters\MappedStdClass');
        $this->setMapper('\RecordsTests\UserFileMapper');
    }
}


class FailedUserRecord1 extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', 777, '456');
    }
}


class FailedUserRecord2 extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 'asdf');
    }
}


class FailedUserRecord3 extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('details', IEntryType::TYPE_OBJECT, new \stdClass());
    }
}


class FailedUserRecord4 extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('details', IEntryType::TYPE_OBJECT, '\stdClass');
    }
}


class UserFileMapper extends Mappers\File\ATable
{
    protected function setMap(): void
    {
        $this->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'users.txt');
        $this->setFormat('\kalanis\kw_mapper\Storage\File\Formats\SeparatedElements');
        $this->setRelation('id', 0);
        $this->setRelation('name', 1);
        $this->setRelation('password', 2);
        $this->setRelation('enabled', 3);
        $this->setRelation('details', 4);
        $this->addPrimaryKey('id');
    }
}

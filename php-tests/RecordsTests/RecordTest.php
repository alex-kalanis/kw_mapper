<?php

namespace RecordsTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;


class RecordTest extends CommonTestClass
{
    public function testSimple()
    {
        $data = new UserRecord();
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

        $data2 = clone $data;
        $data2->name = 'zgvtfcrdxesy';
        $data2->password = 'mnbvcxy';
        $this->assertEquals('zgvtfcrdxesy', $data2['name']);
        $this->assertEquals('mnbvcxy', $data2['password']);
        $this->assertNotEquals('zgvtfcrdxesy', $data['name']);
        $this->assertNotEquals('mnbvcxy', $data['password']);
        $this->assertEquals('plokmijnuhb', $data->name);
        $this->assertEquals('lkjhgfdsa', $data->password);

        $objectEntry = $data->getEntry('details');
        $this->assertEquals(IType::TYPE_OBJECT, $objectEntry->getType());
        $this->assertInstanceOf('\kalanis\kw_mapper\Interfaces\ICanFill', $objectEntry->getData());

        foreach ($data as $key => $entry) {
            $this->assertNotEmpty($key);
            $this->assertNotEmpty($entry);
            $this->assertInstanceOf('\kalanis\kw_mapper\Records\Entry', $data->getEntry($key));
        }

        $data->details = 'another piece';
        $this->assertEquals('another piece', $data->details);
    }

    public function testCannotAddLater()
    {
        $data = new UserRecord();
        $this->expectException(MapperException::class);
        $data['expect'] = 'nothing';
    }

    public function testCannotRemove()
    {
        $data = new UserRecord();
        $this->expectException(MapperException::class);
        unset($data['password']);
    }

    public function testLimitBoolType()
    {
        $data = new UserRecord();
        $data->enabled = null;
        $this->expectException(MapperException::class);
        $data->enabled = 'yes';
    }

    public function testLimitIntType()
    {
        $data = new UserRecord();
        $data['id'] = null;
        $this->expectException(MapperException::class);
        $data['id'] = 'yes';
    }

    public function testLimitIntSize()
    {
        $data = new UserRecord();
        $data['id'] = 8888;
        $this->expectException(MapperException::class);
        $data['id'] = 8889;
    }

    public function testLimitStringType()
    {
        $data = new UserRecord();
        $data['password'] = null;
        $this->expectException(MapperException::class);
        $data['password'] = new \stdClass();
    }

    public function testLimitStringSize()
    {
        $data = new UserRecord();
        $data['password'] = 'poiuztrelkjhgfds';
        $this->expectException(MapperException::class);
        $data['password'] = 'poiuztrelkjhgfdsa';
    }

    public function testBadSet0()
    {
        $this->expectException(MapperException::class);
        new FailedUserRecord0();
    }

    public function testBadSet1()
    {
        $this->expectException(MapperException::class);
        new FailedUserRecord1();
    }

    public function testBadSet2()
    {
        $this->expectException(MapperException::class);
        new FailedUserRecord2();
    }

    public function testBadSet3()
    {
        $this->expectException(MapperException::class);
        new FailedUserRecord3();
    }
}


/**
 * Class UserRecord
 * @package RecordsTests
 * @property int id
 * @property string name
 * @property string password
 * @property bool enabled
 * @property \kalanis\kw_mapper\Adapters\MappedStdClass details
 */
class UserRecord extends ARecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IType::TYPE_INTEGER, 8888); // max size of inner number is 8888
        $this->addEntry('name', IType::TYPE_STRING, 128);
        $this->addEntry('password', IType::TYPE_STRING, 16); // max length of string is 16 chars
        $this->addEntry('enabled', IType::TYPE_BOOLEAN);
        $this->addEntry('details', IType::TYPE_OBJECT, '\kalanis\kw_mapper\Adapters\MappedStdClass');
        $this->setMapper('\RecordsTests\UserFileMapper');
    }
}


class FailedUserRecord0 extends ARecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', 777, '456');
    }
}


class FailedUserRecord1 extends ARecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IType::TYPE_INTEGER, 'asdf');
    }
}


class FailedUserRecord2 extends ARecord
{
    protected function addEntries(): void
    {
        $this->addEntry('details', IType::TYPE_OBJECT, new \stdClass());
    }
}


class FailedUserRecord3 extends ARecord
{
    protected function addEntries(): void
    {
        $this->addEntry('details', IType::TYPE_OBJECT, '\stdClass');
    }
}


//class UserFileMapper extends FileMapper
//{
//    protected function setMap(): void
//    {
//        $this->setFile('users.txt');
//        $this->setMode('|', PHP_EOL);
//        $this->setPosition('id', 0);
//        $this->setPosition('name', 1);
//        $this->setPosition('pass', 2);
//        $this->setPosition('lastLogin', 3);
//        $this->setPrimaryKey('id');
//    }
//}

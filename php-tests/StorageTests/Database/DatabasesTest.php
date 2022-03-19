<?php

namespace StorageTests\Database;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\TConnection;


class DatabasesTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testConnectionRun(): void
    {
        $lib = new XConnect();
        $this->assertFalse($lib->isConnected());
        $this->assertEmpty($lib->getConnection());
        $lib->connect();
        $this->assertTrue($lib->isConnected());
        $this->assertEquals('resource somewhere', $lib->getConnection());
        $lib->reconnect();
        $this->assertTrue($lib->isConnected());
    }
}


class XConnect
{
    use TConnection;

    public function connect(): void
    {
        $this->connection = 'resource somewhere';
    }
}

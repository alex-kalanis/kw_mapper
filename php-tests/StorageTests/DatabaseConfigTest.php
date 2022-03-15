<?php

namespace StorageTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\Storage\Database\Config;


class DatabaseConfigTest extends CommonTestClass
{
    public function testProcess()
    {
        $conf = Config::init()->setTarget(
            IDriverSources::TYPE_PDO_MSSQL,
            'test_conf',
            ':--memory--:',
            12345678,
            'foo',
            'bar',
            'baz'
        );
        $conf->setParams(3600, false);

        $this->assertEquals(IDriverSources::TYPE_PDO_MSSQL, $conf->getDriver());
        $this->assertEquals(':--memory--:', $conf->getLocation());
        $this->assertEquals('test_conf', $conf->getSourceName());
        $this->assertEquals(12345678, $conf->getPort());
        $this->assertEquals('foo', $conf->getUser());
        $this->assertEquals('bar', $conf->getPassword());
        $this->assertEquals('baz', $conf->getDatabase());
        $this->assertFalse($conf->isPersistent());
        $this->assertEquals(3600, $conf->getTimeout());
        $this->assertEquals(null, $conf->getType());

        $conf->setParams(22, true, 'edcrfvtgb');
        $this->assertTrue($conf->isPersistent());
        $this->assertEquals(22, $conf->getTimeout());
        $this->assertEquals('edcrfvtgb', $conf->getType());
    }
}

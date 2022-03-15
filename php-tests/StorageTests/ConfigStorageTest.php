<?php

namespace StorageTests;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Config;
use kalanis\kw_mapper\Storage\Database\ConfigStorage;


class ConfigStorageTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testBasicStore()
    {
        $conf1 = new Config();
        $conf1->setTarget('abc', 'def', 'ghi', 123, 'jkl', 'mno', 'pqr');
        $conf2 = new Config();
        $conf2->setTarget('qay', 'wsx', 'edc', 456, 'rfv', 'tgb', 'zhn');
        XConfStorage::clear();
        $lib = XConfStorage::getInstance();
        $lib->addConfig($conf1);
        $lib->addConfig($conf2);
        $this->assertNotEmpty($lib->getConfig('def'));
        $this->assertNotEmpty($lib->getConfig('wsx'));
        $this->assertEquals('ghi', $lib->getConfig('def')->getLocation());
        $this->assertEquals('edc', $lib->getConfig('wsx')->getLocation());
    }

    /**
     * @throws MapperException
     */
    public function testColumnFail()
    {
        $lib = XConfStorage::getInstance();
        $this->expectException(MapperException::class);
        $lib->getConfig('foo');
    }
}


class XConfStorage extends ConfigStorage
{
    public static function clear(): void
    {
        static::$instance = null;
    }
}

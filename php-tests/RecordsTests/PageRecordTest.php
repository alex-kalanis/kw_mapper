<?php

namespace RecordsTests;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\PageRecord;
use kalanis\kw_storage\Storage\Key\DirKey;


class PageRecordTest extends CommonTestClass
{
    public function setUp(): void
    {
        DirKey::setDir(realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data') . DIRECTORY_SEPARATOR);
        $pt = (new DirKey())->fromSharedKey($this->mockFile());
        if (is_file($pt)) {
            chmod($pt, 0555);
            unlink($pt);
        }

        parent::setUp();
    }

    public function tearDown(): void
    {
        $pt = (new DirKey())->fromSharedKey($this->mockFile());
        if (is_file($pt)) {
            chmod($pt, 0555);
            unlink($pt);
        }
        parent::tearDown();
        DirKey::setDir('');
    }

    /**
     * @throws MapperException
     */
    public function testSimple(): void
    {
        $data = new PageRecordMock();
        $this->assertEmpty($data->path);
        $this->assertEmpty($data->content);

        $data->path = $this->mockFile();
        $data->content = 'qwertzuiopasdfghjklyxcvbnm123456790';
        $this->assertNotEmpty($data->path);
        $this->assertNotEmpty($data->content);

        $this->assertTrue($data->save(true));
        $this->assertTrue($data->load());
        $this->assertEquals(1, $data->count());
        $this->assertEquals(1, count($data->loadMultiple()));

        $ld = new DirKey();
        $this->assertTrue(file_exists($ld->fromSharedKey($this->mockFile())));
        $this->assertTrue($data->delete());
    }

    protected function mockFile(): string
    {
        return 'record_test.txt';
    }
}


class PageRecordMock extends PageRecord
{
    /**
     * @throws MapperException
     * @return bool
     */
    public function insert(): bool
    {
        return $this->mapper->insert($this->getSelf());
    }
}

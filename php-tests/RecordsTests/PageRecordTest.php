<?php

namespace RecordsTests;


use CommonTestClass;
use kalanis\kw_mapper\Records\PageRecord;


class PageRecordTest extends CommonTestClass
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

    public function testSimple()
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
        $this->assertTrue(file_exists($this->mockFile()));
        $this->assertTrue($data->delete());

    }

    protected function mockFile(): string
    {
        return implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'data', 'record_test.txt']);
    }
}


class PageRecordMock extends PageRecord
{
        /**
         * @return bool
         * @throws MapperException
         */
    public function insert(): bool
    {
        return $this->mapper->insert($this->getSelf());
    }

}

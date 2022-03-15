<?php

namespace StorageTests;


use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage;


class BindNameTest extends CommonTestClass
{
    /**
     * @param string $query
     * @param string[] $params
     * @param string $expect
     * @param string[] $binds
     * @param string[] $types
     * @throws MapperException
     * @dataProvider bindProvider
     */
    public function testBindNames(string $query, array $params, string $expect, array $binds, array $types): void
    {
        $lib = new BindName();
        $result = $lib->bindFromNamedToQuestions($query, $params);
        $this->assertEquals($expect, $result[0]);
        $this->assertEquals($binds, $result[1]);
        $this->assertEquals($types, $result[2]);
    }

    public function bindProvider(): array
    {
        return [
            ['abc def ghi jkl', [], 'abc def ghi jkl', [], []],
            ['abc def ghi jkl', [':xyz' => 'tgbzhn'], 'abc def ghi jkl', [], []],
            ['abc :def ghi :jkl', [':def' => 2, ':jkl' => 'rdxesy'], 'abc ? ghi ?', [2, 'rdxesy'], ['i', 's']],
            ['abc :def ghi :jkl', [':def' => true, ':jkl' => 2.22], 'abc ? ghi ?', [1, 2.22], ['i', 'd']],
        ];
    }

    public function testBindNameFail(): void
    {
        $lib = new BindName();
        $this->expectException(MapperException::class);
        $lib->bindFromNamedToQuestions('abc :def ghi jkl', [':xyz' => 'tgbzhn']);
    }
}


class BindName
{
    use Storage\Database\TBindNames;
}

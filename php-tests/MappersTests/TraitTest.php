<?php

namespace MappersTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Storage;


class TraitTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testContentOk(): void
    {
        $data = new Content();
        $data->setContentKey('lkjhgfd');
        $this->assertEquals('lkjhgfd', $data->getContentKey());
    }

    /**
     * @throws MapperException
     */
    public function testContentFail(): void
    {
        $data = new Content();
        $this->expectException(MapperException::class);
        $data->getContentKey();
    }

    /**
     * @param mixed $want
     * @param int $type
     * @param mixed $input
     * @dataProvider typesFromProvider
     */
    public function testTypesFrom($want, int $type, $input): void
    {
        $data = new Translate();
        $this->assertEquals($want, $data->from($type, $input));
    }

    public function typesFromProvider(): array
    {
        return [
            [false, IEntryType::TYPE_BOOLEAN, 0],
            [false, IEntryType::TYPE_BOOLEAN, ''],
            [true, IEntryType::TYPE_BOOLEAN, 7],
            [true, IEntryType::TYPE_BOOLEAN, '3'],
            [15, IEntryType::TYPE_INTEGER, 15.3],
            [4358, IEntryType::TYPE_INTEGER, '4358'],
            [18.8, IEntryType::TYPE_FLOAT, '18.8'],
            [18.8, IEntryType::TYPE_FLOAT, '18.8'],
            [['foo', 'bar'], IEntryType::TYPE_ARRAY, 'a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}'],
            ['lkjhgdf', IEntryType::TYPE_STRING, 'lkjhgdf'],
        ];
    }

    /**
     * @param $want
     * @param int $type
     * @param $input
     * @dataProvider typesToProvider
     */
    public function testTypesTo($want, int $type, $input): void
    {
        $data = new Translate();
        $this->assertEquals($want, $data->to($type, $input));
    }

    public function typesToProvider(): array
    {
        return [
            ['0', IEntryType::TYPE_BOOLEAN, false],
            ['1', IEntryType::TYPE_BOOLEAN, true],
            ['15', IEntryType::TYPE_INTEGER, 15],
            ['18.8', IEntryType::TYPE_FLOAT, 18.8],
            ['a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}', IEntryType::TYPE_ARRAY, ['foo', 'bar']],
            ['lkjhgdf', IEntryType::TYPE_STRING, 'lkjhgdf'],
        ];
    }

    public function testPks(): void
    {
        $pk = new Pk();
        $pk->addPrimaryKey('foo');
        $pk->addPrimaryKey('bar');
        $contains = $pk->getPrimaryKeys();
        $this->assertEquals(2, count($contains));
        $this->assertEquals('foo', reset($contains));
        $this->assertEquals('bar', next($contains));

        $this->assertTrue($pk->filterPrimary('baz', 'bar'));
        $this->assertFalse($pk->filterPrimary('uhb', 'nhz'));
        $this->assertFalse($pk->filterPrimary('uhb', ''));
    }

    public function testFks(): void
    {
        $fk = new Fk();
        $fk->addForeignKey('foo', '\Record', 'local', 'remote');
        $fk->addForeignKey('bar', '\Record', 'baz', 'remote');
        $contains = $fk->getForeignKeys();
        $this->assertEquals(2, count($contains));
        $entry = reset($contains);
        $this->assertEquals('foo', $entry->getLocalAlias());
        $this->assertEquals('\Record', $entry->getRemoteRecord());
        $this->assertEquals('local', $entry->getLocalEntryKey());
        $this->assertEquals('remote', $entry->getRemoteEntryKey());
        $entry = next($contains);
        $this->assertEquals('bar', $entry->getLocalAlias());
        $this->assertEquals('\Record', $entry->getRemoteRecord());
        $this->assertEquals('baz', $entry->getLocalEntryKey());
        $this->assertEquals('remote', $entry->getRemoteEntryKey());
    }

    public function testRelations(): void
    {
        $relations = new Relations();
        $relations->setRelation('foo', 'local');
        $relations->setRelation('bar', 'baz');
        $contains = $relations->getRelations();
        $this->assertEquals(2, count($contains));
        $this->assertEquals('local', reset($contains));
        $this->assertEquals('foo', key($contains));
        $this->assertEquals('baz', next($contains));
        $this->assertEquals('bar', key($contains));
    }

    public function testSource(): void
    {
        $source = new Source();
        $source->setSource('foo');
        $this->assertEquals('foo', $source->getSource());
        $source->setSource('bar');
        $this->assertEquals('bar', $source->getSource());
    }

    public function testDbTable(): void
    {
        $source = new Table();
        $source->setTable('foo');
        $this->assertEquals('foo', $source->getTable());
        $source->setTable('bar');
        $this->assertEquals('bar', $source->getTable());
    }
}


class Content
{
    use Mappers\Shared\TContent;
}


class Translate
{
    use Mappers\Shared\TTranslate;

    public function from(int $type, $content)
    {
        return $this->translateTypeFrom($type, $content);
    }

    public function to(int $type, $content)
    {
        return $this->translateTypeTo($type, $content);
    }
}


class Table
{
    use Mappers\Database\TTable;
}


class Pk
{
    use Mappers\Shared\TPrimaryKey;
}


class Fk
{
    use Mappers\Shared\TForeignKey;
}


class Relations
{
    use Mappers\Shared\TRelations;
}


class Source
{
    use Mappers\Shared\TSource;
}

<?php

namespace MappersTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Search\Search;


class PresetTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testContentOk(): void
    {
        $lib = new PresetRecord();
        $this->assertEquals('preset', $lib->getMapper()->getAlias());

        $rec = new PresetRecord();
        $rec->what = 'bar';
        $rec->load();
        $this->assertEquals('err', $rec->when);

        $rec2 = new PresetRecord();
        $this->assertEquals(3, $rec2->count());
    }

    /**
     * @throws MapperException
     */
    public function testSearch(): void
    {
        $lib = new Search(new PresetRecord());
        $lib->like('what', 'a');
        $this->assertEquals(2, $lib->getCount());

        $results = $lib->getResults();
        /** @var PresetRecord $rec */
        $rec = reset($results);
        $this->assertEquals('bar', $rec->what);
        $rec = next($results);
        $this->assertEquals('baz', $rec->what);
    }

    /**
     * @throws MapperException
     */
    public function testCannotInsert(): void
    {
        $rec = new PresetRecord();
        $rec->what = 'out';
        $rec->when = 'nop';
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Cannot insert record into predefined array');
        $rec->save();
    }

    /**
     * @throws MapperException
     */
    public function testCannotUpdate(): void
    {
        $rec = new PresetRecord();
        $rec->getEntry('what')->setData('baz', true);
        $rec->when = 'syn';
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Cannot update record in predefined array');
        $rec->save();
    }

    /**
     * @throws MapperException
     */
    public function testCannotDelete(): void
    {
        $rec = new PresetRecord();
        $rec->what = 'baz';
        $this->expectException(MapperException::class);
        $this->expectExceptionMessage('Cannot delete record in predefined array');
        $rec->delete();
    }

    /**
     * @throws MapperException
     */
    public function testCannotFind(): void
    {
        $rec = new PresetRecord();
        $rec->what = 'nop';
        $this->assertFalse($rec->load());
    }
}


/**
 * Class PresetRecord
 * @package MappersTests
 * @property int $id
 * @property string $what
 * @property string $when
 */
class PresetRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 10);
        $this->addEntry('what', IEntryType::TYPE_STRING, 32);
        $this->addEntry('when', IEntryType::TYPE_STRING, 32);
        $this->setMapper(PresetMapper::class);
    }
}


class PresetMapper extends Mappers\APreset
{
    protected function setMap(): void
    {
        $this->setSource('preset');
        $this->setRelation('id', 0);
        $this->setRelation('what', 1);
        $this->setRelation('when', 2);
        $this->addPrimaryKey('id');
    }

    protected function loadFromStorage(): array
    {
        return [
            [1, 'foo', 'ack'],
            [2, 'bar', 'err'],
            [3, 'baz', 'syn'],
        ];
    }
}

<?php

namespace MappersTests;


use CommonTestClass;
use kalanis\kw_mapper\Adapters\DataExchange;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\ASimpleRecord;


class FinderTest extends CommonTestClass
{
    /**
     * @param ARecord $source
     * @param bool $pks
     * @param bool $storage
     * @param array $result
     * @throws MapperException
     * @dataProvider compareDefaultProvider
     */
    public function testWhatCompareDefault(ARecord $source, bool $pks, bool $storage, array $result): void
    {
        $lib = new XFinder();
        $this->assertEquals($result, $lib->arrayToCompare($source, $pks, $storage));
    }

    public function compareDefaultProvider(): array
    {
        $simple = new FinderRecord();
        $simple->id = 5;
        $simple->when = 'oof';
        $simple->what = 'how';

        $loaded = new FinderRecord();
        $loaded->id = 5;
        $loaded->getEntry('when')->setData('oof', true);
        $loaded->what = 'how';

        return [
            [$simple, false, false, ['id' => 5, 'when' => 'oof', 'what' => 'how']],
            [$loaded, false, false, ['id' => 5, 'when' => 'oof', 'what' => 'how']],
            [$simple, false, true, ['id' => 5, 'when' => 'oof', 'what' => 'how']],
            [$loaded, false, true, ['when' => 'oof']],
            [$simple, true, false, ['id' => 5]],
            [$loaded, true, false, ['id' => 5]],
            [$simple, true, true, ['id' => 5]],
            [$loaded, true, true, ['id' => 5]],
        ];
    }

    /**
     * @throws MapperException
     */
    public function testFindSome(): void
    {
        $wanted = new FinderRecord();
        $wanted->when = 'err';
        $finder = new XFinder();
        $search = $finder->find($wanted);
        $this->assertEquals(1, count($search));
        $entry = reset($search);
        /** @var FinderRecord $entry */
        $this->assertEquals(2, $entry->id);
    }

    /**
     * @throws MapperException
     */
    public function testFindNone(): void
    {
        $wanted = new FinderRecord();
        $wanted->when = 'oof';
        $finder = new XFinder();
        $search = $finder->find($wanted);
        $this->assertEquals(0, count($search));
    }

    /**
     * @throws MapperException
     */
    public function testFindNoneExt(): void
    {
        $wanted = new PlusFinderRecord();
        $wanted->extra = 'not_set';
        $finder = new XFinder();
        $search = $finder->find($wanted);
        $this->assertEquals(0, count($search));
    }
}


class XFinder
{
    use Mappers\Shared\TFinder;
    use Mappers\Shared\TPrimaryKey;
    use Mappers\Shared\TRelations;

    /**
     * @param ARecord $record
     * @param bool $usePks
     * @param bool $wantFromStorage
     * @throws MapperException
     * @return ARecord[]
     */
    public function find(ARecord $record, bool $usePks = false, bool $wantFromStorage = false): array
    {
        return $this->findMatched($record, $usePks, $wantFromStorage);
    }

    /**
     * @param ARecord $record
     * @param bool $usePks
     * @param bool $wantFromStorage
     * @throws MapperException
     * @return array<string|int, mixed>
     */
    public function arrayToCompare(ARecord $record, bool $usePks, bool $wantFromStorage): array
    {
        return $this->getArrayToCompare($record, $usePks, $wantFromStorage);
    }

    protected function loadSource(ARecord $record): array
    {
        return [
            $this->getRecord(['id' => 1, 'what' => 'foo', 'when' => 'ack']),
            $this->getRecord(['id' => 2, 'what' => 'bar', 'when' => 'err']),
            $this->getRecord(['id' => 3, 'what' => 'baz', 'when' => 'syn']),
        ];
    }

    /**
     * @param array<string, string|int> $values
     * @throws MapperException
     * @return ARecord
     */
    protected function getRecord(array $values): ARecord
    {
        $ex = new DataExchange(new FinderRecord());
        $ex->import($values);
        return $ex->getRecord();
    }
}


/**
 * Class FinderRecord
 * @package MappersTests
 * @property int $id
 * @property string $what
 * @property string $when
 */
class FinderRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 10);
        $this->addEntry('what', IEntryType::TYPE_STRING, 32);
        $this->addEntry('when', IEntryType::TYPE_STRING, 32);
        $this->setMapper(FinderMapper::class); // probably not necessary here
    }
}


class FinderMapper extends Mappers\APreset
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
            [1, 'foo', 'ack', '', '0'],
            [2, 'bar', 'err', '', '1'],
            [3, 'baz', 'syn', '', '1'],
        ];
    }
}


/**
 * Class PlusFinderRecord
 * @package MappersTests
 * @property string $extra
 * @property string $plus
 */
class PlusFinderRecord extends FinderRecord
{
    protected function addEntries(): void
    {
        parent::addEntries();
        $this->addEntry('extra', IEntryType::TYPE_STRING, 32);
        $this->addEntry('plus', IEntryType::TYPE_BOOLEAN, false);
        $this->setMapper(PlusFinderMapper::class); // probably not necessary here
    }
}


class PlusFinderMapper extends FinderMapper
{
    protected function setMap(): void
    {
        parent::setMap();
        $this->setRelation('extra', 3);
        $this->setRelation('extra', 4);
    }
}

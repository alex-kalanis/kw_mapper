<?php

namespace kalanis\kw_mapper\Search;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;


/**
 * Class ASearch
 * @package kalanis\kw_mapper\Search
 * Complex searching (now in database)
 */
abstract class ASearch
{
    /** @var Connector\AConnector */
    protected $connector = null;
    /** @var string */
    protected static $propertySeparator = '.';

    /**
     * @param ARecord $record
     * @param ARecord[] $initialRecords
     * @throws MapperException
     */
    public function __construct(ARecord $record, array $initialRecords = [])
    {
        $this->connector = Connector\Factory::getInstance()->getConnector($record, $initialRecords);
    }

    protected function parseProperty(string $property): array
    {
        $separated = explode(static::$propertySeparator, $property, 2);
        return ( (1 < count($separated)) && mb_strlen($separated[0]) && mb_strlen($separated[1]) )
            ? $separated
            : ['', $property]
        ;
    }

    /**
     * @return int
     * @throws MapperException
     */
    public function getCount(): int
    {
        return $this->connector->getCount();
    }

    /**
     * @return ARecord[]
     * @throws MapperException
     */
    public function getResults(): array
    {
        return $this->connector->getResults();
    }
}

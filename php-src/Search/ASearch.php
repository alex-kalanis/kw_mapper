<?php

namespace kalanis\kw_mapper\Search;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


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
     * @param QueryBuilder|null $builder
     * @throws MapperException
     */
    public function __construct(ARecord $record, array $initialRecords = [], ?QueryBuilder $builder = null)
    {
        $this->connector = Connector\Factory::getInstance()->getConnector($record, $initialRecords, $builder);
    }

    /**
     * @param string $property
     * @return string[]
     */
    protected function parseProperty(string $property): array
    {
        $separated = explode(static::$propertySeparator, $property, 2);
        return ((false !== $separated) && (1 < count($separated)) && mb_strlen($separated[0]) && mb_strlen($separated[1]) )
            ? $separated
            : ['', $property]
        ;
    }

    /**
     * @throws MapperException
     * @return int
     */
    public function getCount(): int
    {
        return $this->connector->getCount();
    }

    /**
     * @throws MapperException
     * @return ARecord[]
     */
    public function getResults(): array
    {
        return $this->connector->getResults();
    }
}

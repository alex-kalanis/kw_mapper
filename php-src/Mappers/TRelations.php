<?php

namespace kalanis\kw_mapper\Mappers;


/**
 * Class AMapper
 * @package kalanis\kw_mapper\Mappers
 * Simple work with relations
 */
trait TRelations
{
    /** @var array<string|int, string|int> */
    protected $relations = [];

    /**
     * @param string $localAlias
     * @param string|int $remoteKey
     */
    public function setRelation(string $localAlias, $remoteKey): void
    {
        $this->relations[$localAlias] = $remoteKey;
    }

    /**
     * @return array<string|int, string|int>
     */
    public function getRelations(): array
    {
        return $this->relations;
    }
}

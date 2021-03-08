<?php

namespace kalanis\kw_mapper\Mappers;


/**
 * Class AMapper
 * @package kalanis\kw_mapper\Mappers
 * Simple work with relations
 */
trait TRelations
{
    protected $relations = [];

    public function setRelation(string $localAlias, $remoteKey): void
    {
        $this->relations[$localAlias] = $remoteKey;
    }

    public function getRelations(): array
    {
        return $this->relations;
    }
}

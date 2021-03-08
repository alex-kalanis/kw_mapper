<?php

namespace kalanis\kw_mapper\Mappers;


/**
 * Trait TPrimaryKey
 * @package kalanis\kw_mapper\Mappers
 * Simple work with primary keys
 * There can be multiple of them as combined PKs
 */
trait TPrimaryKey
{
    protected $primaryKeys = [];

    public function addPrimaryKey(string $localAlias): void
    {
        $this->primaryKeys[] = $localAlias;
    }

    public function getPrimaryKeys(): array
    {
        return $this->primaryKeys;
    }

    public function filterPrimary($v, $k): bool
    {
        return in_array($k, $this->primaryKeys) && !empty($v);
    }
}

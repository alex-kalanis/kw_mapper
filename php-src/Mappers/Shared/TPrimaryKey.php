<?php

namespace kalanis\kw_mapper\Mappers\Shared;


/**
 * Trait TPrimaryKey
 * @package kalanis\kw_mapper\Mappers\Shared
 * Simple work with primary keys
 * There can be multiple of them as combined PKs
 */
trait TPrimaryKey
{
    /** @var string[] */
    protected $primaryKeys = [];

    public function addPrimaryKey(string $localAlias): void
    {
        $this->primaryKeys[] = $localAlias;
    }

    /**
     * @return string[]
     */
    public function getPrimaryKeys(): array
    {
        return $this->primaryKeys;
    }

    /**
     * @param mixed $v
     * @param string|int $k
     * @return bool
     */
    public function filterPrimary($v, $k): bool
    {
        return in_array($k, $this->primaryKeys) && !empty($v);
    }
}

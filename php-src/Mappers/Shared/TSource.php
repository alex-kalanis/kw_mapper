<?php

namespace kalanis\kw_mapper\Mappers\Shared;


/**
 * Trait TSource
 * @package kalanis\kw_mapper\Mappers\Shared
 */
trait TSource
{
    /** @var string */
    protected $tableSource = '';

    public function setSource(string $tableSource): void
    {
        $this->tableSource = $tableSource;
    }

    public function getSource(): string
    {
        return $this->tableSource;
    }
}

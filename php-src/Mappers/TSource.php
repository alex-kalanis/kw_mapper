<?php

namespace kalanis\kw_mapper\Mappers;


/**
 * Trait TSource
 * @package kalanis\kw_mapper\Mappers
 */
trait TSource
{
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

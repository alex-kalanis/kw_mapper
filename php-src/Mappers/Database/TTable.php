<?php

namespace kalanis\kw_mapper\Mappers\Database;


/**
 * Trait TTable
 * @package kalanis\kw_mapper\Mappers\Database
 */
trait TTable
{
    protected $tableName = '';

    public function setTable(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    public function getTable(): string
    {
        return $this->tableName;
    }
}

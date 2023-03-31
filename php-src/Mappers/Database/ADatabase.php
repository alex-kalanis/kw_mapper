<?php

namespace kalanis\kw_mapper\Mappers\Database;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\AMapper;
use kalanis\kw_mapper\Storage;


/**
 * Class ADatabase
 * @package kalanis\kw_mapper\Mappers\Database
 */
abstract class ADatabase extends AMapper
{
    use TTable;
    use TReadDatabase;
    use TWriteDatabase;

    /**
     * @throws MapperException
     */
    public function __construct()
    {
        parent::__construct();

        $this->initTReadDatabase();
        $this->initTWriteDatabase();
    }

    public function getAlias(): string
    {
        return $this->getTable();
    }

    protected function getReadSource(): string
    {
        return $this->getSource();
    }

    protected function getWriteSource(): string
    {
        return $this->getSource();
    }
}

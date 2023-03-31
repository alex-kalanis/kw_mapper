<?php

namespace kalanis\kw_mapper\Mappers\Database;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\AMapper;
use kalanis\kw_mapper\Storage;


/**
 * Class AReadWriteDatabase
 * @package kalanis\kw_mapper\Mappers\Database
 * Separated Read and write DB entry without need to reload mapper
 * The most parts are similar to usual read/write one, just with separation of read-write operations
 */
abstract class AReadWriteDatabase extends AMapper
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
}

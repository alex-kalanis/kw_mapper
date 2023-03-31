<?php

namespace kalanis\kw_mapper\Mappers\Storage;


use kalanis\kw_mapper\Mappers\Shared;


/**
 * Class ATable
 * @package kalanis\kw_mapper\Mappers\Storage
 * Abstract for manipulation with file content as table
 */
abstract class ATable extends AStorage
{
    use Shared\TReadFileTable;
    use Shared\TWriteFileTable;
}

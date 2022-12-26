<?php

namespace kalanis\kw_mapper\Mappers\File;


use kalanis\kw_mapper\Mappers\Shared\TFileTable;


/**
 * Class ATable
 * @package kalanis\kw_mapper\Mappers\File
 * Abstract for manipulation with file content as table
 */
abstract class ATable extends AFileSource
{
    use TFileTable;
}

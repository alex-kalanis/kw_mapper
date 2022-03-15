<?php

use kalanis\kw_mapper\Storage\Database;
use kalanis\kw_mapper\Storage\Shared;
use PHPUnit\Framework\TestCase;


/**
 * Class CommonTestClass
 * The structure for mocking and configuration seems so complicated, but it's necessary to let it be totally idiot-proof
 */
class CommonTestClass extends TestCase
{
}


class Builder extends Shared\QueryBuilder
{
    public function resetCounter(): void
    {
        static::$uniqId = 0;
    }
}


class Builder2 extends Database\QueryBuilder
{
    public function resetCounter(): void
    {
        static::$uniqId = 0;
    }
}

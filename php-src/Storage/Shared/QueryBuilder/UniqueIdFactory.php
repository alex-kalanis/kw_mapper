<?php

namespace kalanis\kw_mapper\Storage\Shared\QueryBuilder;


class UniqueIdFactory
{
    protected static ?self $instance = null;
    protected UniqueId $class;

    public static function getFactoryInstance(): self
    {
        if (empty(static::$instance)) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    protected function __construct()
    {
        $this->class = new UniqueId();
    }

    private function __clone()
    {
    }

    public function getClass(): UniqueId
    {
        return $this->class;
    }
}

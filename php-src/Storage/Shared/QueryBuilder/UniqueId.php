<?php

namespace kalanis\kw_mapper\Storage\Shared\QueryBuilder;


class UniqueId
{
    protected int $id = 0;

    public function get(): int
    {
        $id = $this->id;
        $this->id++;
        return $id;
    }

    public function clear(): self
    {
        $this->id = 0;
        return $this;
    }
}

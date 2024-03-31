<?php

namespace kalanis\kw_mapper\Storage\Shared;


/**
 * Trait TFormat
 * @package kalanis\kw_mapper\Storage\Shared
 */
trait TFormat
{
    protected string $format = '';

    public function setFormat(string $formatClass): self
    {
        $this->format = $formatClass;
        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}

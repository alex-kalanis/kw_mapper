<?php

namespace kalanis\kw_mapper\Storage\File;


/**
 * Trait TFormat
 * @package kalanis\kw_mapper\Storage\File
 */
trait TFormat
{
    protected $format = '';

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

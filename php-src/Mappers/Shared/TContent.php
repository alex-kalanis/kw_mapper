<?php

namespace kalanis\kw_mapper\Mappers\Shared;


use kalanis\kw_mapper\MapperException;


/**
 * Trait TContent
 * @package kalanis\kw_mapper\Mappers\Shared
 */
trait TContent
{
    /** @var string */
    protected $contentKey = '';

    public function setContentKey(string $contentKey): self
    {
        $this->contentKey = $contentKey;
        return $this;
    }

    /**
     * @throws MapperException
     * @return string
     */
    public function getContentKey(): string
    {
        $this->checkContentKey();
        return $this->contentKey;
    }

    /**
     * @throws MapperException
     */
    protected function checkContentKey(): void
    {
        if (empty($this->contentKey)) {
            throw new MapperException('Cannot manipulate content without data key - content itself!');
        }
    }
}

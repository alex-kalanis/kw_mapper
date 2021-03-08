<?php

namespace kalanis\kw_mapper\Records;


use kalanis\kw_mapper\Interfaces\ICanFill;


/**
 * Class Entry
 * @package kalanis\kw_mapper\Records
 * Simple entry to fill
 */
class Entry
{
    protected $type = 0;
    protected $data = null;
    protected $params = null;
    protected $isFromStorage = false;

    public static function getInstance(): Entry
    {
        return new static();
    }

    public function setType(int $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param null|int|string|ICanFill $data
     * @param bool $isFromStorage
     * @return Entry
     */
    public function setData($data, bool $isFromStorage = false): self
    {
        $this->data = $data;
        $this->isFromStorage = $isFromStorage;
        return $this;
    }

    /**
     * @return null|int|string|ICanFill
     */
    public function getData()
    {
        return $this->data;
    }

    public function setParams($params): self
    {
        $this->params = $params;
        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function isFromStorage(): bool
    {
        return $this->isFromStorage;
    }
}

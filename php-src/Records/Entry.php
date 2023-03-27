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
    /** @var int */
    protected $type = 0;
    /** @var null|int|string|float|bool|array<int|string, int|string|float|bool|ARecord|array<int|string, int|string>>|ICanFill|false */
    protected $data = false;
    /** @var string|int|array<string|int, string|int>|null */
    protected $params = null;
    /** @var bool */
    protected $isFromStorage = false;

    public static function getInstance(): self
    {
        return new self();
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
     * @param null|int|string|float|bool|array<int|string, int|string|float|bool|ARecord|array<int|string, int|string>>|ICanFill $data
     * @param bool $isFromStorage
     * @return $this
     */
    public function setData($data, bool $isFromStorage = false): self
    {
        $this->data = $data;
        $this->isFromStorage = $isFromStorage;
        return $this;
    }

    /**
     * @return null|int|string|float|bool|array<int|string, int|string|float|bool|ARecord|array<int|string, int|string>>|ICanFill|false
     * False is for no use - rest is available as data
     * If you want to save false in your db, just cast it through integer
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string|int|array<string|int, string|int>|null $params
     * @return $this
     */
    public function setParams($params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return string|int|array<string|int, string|int>|null
     */
    public function getParams()
    {
        return $this->params;
    }

    public function isFromStorage(): bool
    {
        return $this->isFromStorage;
    }
}

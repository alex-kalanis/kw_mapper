<?php

namespace kalanis\kw_mapper\Search\Connector\Database;


use kalanis\kw_mapper\Records\ARecord;


/**
 * Class Records
 * @package kalanis\kw_mapper\Search\Connector\Database
 * Structure to access records which come in joins
 */
class Records
{
    /** @var ARecord|null */
    protected $record = null;
    protected $currentAlias = '';
    protected $parentAlias = null;
    protected $storeKey = '';

    public function setData(ARecord $record, string $currentAlias, ?string $parentAlias, string $storeKey): self
    {
        $this->record = $record;
        $this->currentAlias = $currentAlias;
        $this->parentAlias = $parentAlias;
        $this->storeKey = $storeKey;
        return $this;
    }

    public function getRecord(): ?ARecord
    {
        return $this->record;
    }

    public function getCurrentAlias(): ?string
    {
        return $this->currentAlias;
    }

    public function getParentAlias(): ?string
    {
        return $this->parentAlias;
    }

    public function getStoreKey(): string
    {
        return $this->storeKey;
    }
}

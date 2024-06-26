<?php

namespace kalanis\kw_mapper\Search\Connector\Database;


use kalanis\kw_mapper\Records\ARecord;


/**
 * Class RecordsInJoin
 * @package kalanis\kw_mapper\Search\Connector\Database
 * Structure to access records which come in joins
 */
class RecordsInJoin
{
    protected ?ARecord $record = null;
    protected ?string $parentAlias = null;
    protected string $storeKey = '';
    protected string $knownAs = '';

    public function setData(ARecord $record, string $storeKey, ?string $parentAlias, string $knownAs = ''): self
    {
        $this->record = $record;
        $this->parentAlias = $parentAlias;
        $this->storeKey = $storeKey;
        $this->knownAs = empty($knownAs) ? $storeKey : $knownAs ;
        return $this;
    }

    public function getRecord(): ?ARecord
    {
        return $this->record;
    }

    public function getParentAlias(): ?string
    {
        return $this->parentAlias;
    }

    public function getStoreKey(): string
    {
        return $this->storeKey;
    }

    public function getKnownAs(): string
    {
        return $this->knownAs;
    }
}

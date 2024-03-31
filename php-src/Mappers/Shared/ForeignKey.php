<?php

namespace kalanis\kw_mapper\Mappers\Shared;


/**
 * Class ForeignKey
 * @package kalanis\kw_mapper\Mappers\Shared
 */
class ForeignKey
{
    protected string $localAlias = '';
    protected string $remoteRecord = '';
    protected string $localEntryKey = '';
    protected string $remoteEntryKey = '';

    public function setData(string $localAlias, string $remoteRecord, string $localEntryKey, string $remoteEntryKey): self
    {
        $this->localAlias = $localAlias;
        $this->remoteRecord = $remoteRecord;
        $this->localEntryKey = $localEntryKey;
        $this->remoteEntryKey = $remoteEntryKey;
        return $this;
    }

    public function getLocalAlias(): string
    {
        return $this->localAlias;
    }

    public function getRemoteRecord(): string
    {
        return $this->remoteRecord;
    }

    public function getLocalEntryKey(): string
    {
        return $this->localEntryKey;
    }

    public function getRemoteEntryKey(): string
    {
        return $this->remoteEntryKey;
    }

}

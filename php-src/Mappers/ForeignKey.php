<?php

namespace kalanis\kw_mapper\Mappers;


/**
 * Class ForeignKey
 * @package kalanis\kw_mapper\Mappers
 */
class ForeignKey
{
    /** @var string */
    protected $localAlias = '';
    /** @var string */
    protected $remoteRecord = '';
    /** @var string */
    protected $localEntryKey = '';
    /** @var string */
    protected $remoteEntryKey = '';

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

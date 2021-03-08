<?php

namespace kalanis\kw_mapper\Mappers;


/**
 * Trait TForeignKey
 * @package kalanis\kw_mapper\Mappers
 * Accessing foreign keys
 */
trait TForeignKey
{
    protected $foreignKeys = [];
    /** @var ForeignKey|null */
    private $foreignKeyClass = null;

    public function addForeignKey(string $localAlias, string $remoteRecord, string $localEntryKey, string $remoteEntryKey): void
    {
        $this->initClassFks();
        $foreignKeyClass = clone $this->foreignKeyClass;
        $this->foreignKeys[$localAlias] = $foreignKeyClass->setData($localAlias, $remoteRecord, $localEntryKey, $remoteEntryKey);
    }

    private function initClassFks(): void
    {
        if (empty($this->foreignKeyClass)) {
            $this->foreignKeyClass = new ForeignKey();
        }
    }

    /**
     * @return ForeignKey[]
     */
    public function getForeignKeys(): array
    {
        return $this->foreignKeys;
    }
}

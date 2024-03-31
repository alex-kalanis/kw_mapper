<?php

namespace kalanis\kw_mapper\Mappers\Database;


use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\AMapper;
use kalanis\kw_mapper\Mappers\Shared\TContent;
use kalanis\kw_mapper\Records;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\TFill;
use kalanis\kw_mapper\Storage;


/**
 * Class WinRegistry2
 * @package kalanis\kw_mapper\Mappers\Database
 * We are not crazy enough - let's work with Windows Registry! In PHP.
 * - the path is tree and there is bunch of keys; it's composed primary key - part (HK*_*) and path
 * - the difference with registry and normal file is simple - there is also content type, not just path and content
 * -> it's similar to flags in *nix or app rights in OS9
 *
 * The registry is hybrid between usual database and filesystem - storing values in tree nodes like FS and with type
 * control like DB. That means there is specific query set
 * This class uses .Net platform to access data in registry.
 * @codeCoverageIgnore cannot check this on *nix
 */
class WinRegistry2 extends AMapper
{
    use TContent;
    use TFill;

    protected string $typeKey = '';
    protected Storage\Database\Raw\WinRegistry2 $database;

    /**
     * @throws MapperException
     */
    public function __construct()
    {
        parent::__construct();
        $config = Storage\Database\ConfigStorage::getInstance()->getConfig($this->getSource());
        $this->database = Storage\Database\DatabaseSingleton::getInstance()->getDatabase($config);
    }

    public function getAlias(): string
    {
        return 'win_registry2';
    }

    protected function setMap(): void
    {
        $this->addPrimaryKey('part');
        $this->addPrimaryKey('path');
        $this->setTypeKey('type');
        $this->setContentKey('content');
    }

    public function setTypeKey(string $typeKey): self
    {
        $this->typeKey = $typeKey;
        return $this;
    }

    public function getTypeKey(): string
    {
        return $this->typeKey;
    }

    /**
     * @param Records\ARecord|Records\RegistryRecord $record
     * @throws MapperException
     * @return bool
     */
    protected function insertRecord(Records\ARecord $record): bool
    {
        $pks = $this->getPrimaryKeys();
        return $this->database->exec(
            IDriverSources::ACTION_INSERT,
            $record->offsetGet(reset($pks)),
            $record->offsetGet(next($pks)),
            $record->offsetGet($this->getTypeKey()),
            $record->offsetGet($this->getContentKey())
        );
    }

    protected function updateRecord(ARecord $record): bool
    {
        $pks = $this->getPrimaryKeys();
        return $this->database->exec(
            IDriverSources::ACTION_UPDATE,
            $record->offsetGet(reset($pks)),
            $record->offsetGet(next($pks)),
            $record->offsetGet($this->getTypeKey()),
            $record->offsetGet($this->getContentKey())
        );
    }

    protected function deleteRecord(ARecord $record): bool
    {
        $pks = $this->getPrimaryKeys();
        return $this->database->exec(
            IDriverSources::ACTION_DELETE,
            $record->offsetGet(reset($pks)),
            $record->offsetGet(next($pks)),
            $record->offsetGet($this->getTypeKey())
        );
    }

    protected function loadRecord(ARecord $record): bool
    {
        $pks = $this->getPrimaryKeys();
        $values = $this->database->value(
            $record->offsetGet(reset($pks)),
            $record->offsetGet(next($pks))
        );

        if (empty($values)) { // nothing found
            return false;
        }

        $value = reset($values);

        // fill entries in record
        $entry = $record->getEntry($this->getTypeKey());
        $entry->setData($this->typedFillSelection($entry, reset($value)), true);
        $entry = $record->getEntry($this->getContentKey());
        $entry->setData($this->typedFillSelection($entry, next($value)), true);

        return true;
    }

    public function countRecord(ARecord $record): int
    {
        throw new MapperException('You cannot count keys in registry!');
    }

    public function loadMultiple(ARecord $record): array
    {
        throw new MapperException('You cannot get multiple keys from registry!');
    }
}

<?php

namespace kalanis\kw_mapper\Mappers\Database;


use kalanis\kw_mapper\Interfaces\ICanFill;
use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\AMapper;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\TFill;
use kalanis\kw_mapper\Storage;


/**
 * Class ALdap
 * @package kalanis\kw_mapper\Mappers\Database
 * @codeCoverageIgnore for now - external source
 */
abstract class ALdap extends AMapper
{
    use TFill;
    use TTable;

    /** @var Storage\Database\Raw\Ldap */
    protected $database = null;
    /** @var Storage\Shared\QueryBuilder */
    protected $queryBuilder = null;
    /** @var Storage\Database\Dialects\LdapQueries */
    protected $dialect = null;

    /**
     * @throws MapperException
     */
    public function __construct()
    {
        parent::__construct();
        $config = Storage\Database\ConfigStorage::getInstance()->getConfig($this->getSource());
        $this->database = Storage\Database\DatabaseSingleton::getInstance()->getDatabase($config);
        $this->dialect = new Storage\Database\Dialects\LdapQueries();
        $this->queryBuilder = new Storage\Shared\QueryBuilder();
    }

    public function getAlias(): string
    {
        return $this->getTable();
    }

    protected function insertRecord(ARecord $record): bool
    {
        $this->queryBuilder->clear();
        $this->queryBuilder->setBaseTable($this->getTable());
        foreach ($record as $key => $item) {
            $this->queryBuilder->addProperty($this->getTable(), $this->relations[$key], $item);
        }
        $this->database->connect();
        $connect = $this->database->getConnection();
        if (!(is_resource($connect) || is_object($connect))) {
            return false;
        }
        return ldap_add(
            $connect,
            $this->dialect->domainDn($this->database->getDomain()),
            $this->dialect->changed($this->queryBuilder)
        );
    }

    protected function updateRecord(ARecord $record): bool
    {
        $this->queryBuilder->clear();
        $this->queryBuilder->setBaseTable($this->getTable());
        foreach ($record as $key => $item) {
            if (!$record->getEntry($key)->isFromStorage()) {
                $this->queryBuilder->addProperty($this->getTable(), $this->relations[$key], $item);
            }
        }
        $this->database->connect();
        $connect = $this->database->getConnection();
        if (!(is_resource($connect) || is_object($connect))) {
            return false;
        }
        return ldap_mod_replace(
            $connect,
            $this->dialect->userDn($this->database->getDomain(), $this->getPk($record)),
            $this->dialect->changed($this->queryBuilder)
        );
    }

    protected function deleteRecord(ARecord $record): bool
    {
        $this->database->connect();
        $connect = $this->database->getConnection();
        if (!(is_resource($connect) || is_object($connect))) {
            return false;
        }
        return ldap_delete(
            $connect,
            $this->dialect->userDn($this->database->getDomain(), $this->getPk($record))
        );
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return string
     */
    protected function getPk(ARecord $record)
    {
        $pks = $this->getPrimaryKeys();
        $pk = reset($pks);
        $off = $record->offsetGet($pk);
        return ($off instanceof ICanFill) ? strval($off->dumpData()) : strval($off);
    }

    protected function loadRecord(ARecord $record): bool
    {
        $this->fillConditions($record);
        $lines = $this->multiple();
        if (empty($lines) || empty($lines[0]) || !is_iterable($lines[0])) { // nothing found
            return false;
        }

        // fill entries in record
        $relationMap = array_flip($this->relations);
        foreach ($lines[0] as $index => $item) {
            $entry = $record->getEntry($relationMap[$index]);
            $entry->setData($this->typedFillSelection($entry, $this->readItem($item)), true);
        }
        return true;
    }

    public function countRecord(ARecord $record): int
    {
        $this->fillConditions($record);
        $entries = $this->multiple();
        if (empty($entries) || empty($entries['count'])) {
            return 0;
        }
        return intval($entries['count']);
    }

    public function loadMultiple(ARecord $record): array
    {
        $this->fillConditions($record);
        $lines = $this->multiple();
        if (empty($lines)) {
            return [];
        }

        $result = [];
        $relationMap = array_flip($this->relations);
        foreach ($lines as $key => $line) {
            if (is_numeric($key) && is_iterable($line)) {
                $rec = clone $record;
                foreach ($line as $index => $item) {
                    $entry = $rec->getEntry($relationMap[$index]);
                    $entry->setData($this->typedFillSelection($entry, $this->readItem($item)), true);
                }
                $result[] = $rec;
            }
        }
        return $result;
    }

    /**
     * @param mixed $item
     * @return string
     */
    protected function readItem($item)
    {
        return (empty($item) || empty($item[0]) || ('NULL' == $item[0])) ? '' : $item[0];
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     */
    protected function fillConditions(ARecord $record): void
    {
        $this->queryBuilder->clear();
        $this->queryBuilder->setBaseTable($this->getTable());
        foreach ($record as $key => $item) {
            if (false !== $item) {
                $this->queryBuilder->addCondition($this->getTable(), $this->relations[$key], IQueryBuilder::OPERATION_EQ, $item);
            }
        }
    }

    /**
     * @throws MapperException
     * @return array<string|int, string|int|float|array<string|int|float>>
     */
    protected function multiple(): array
    {
        $this->database->connect();
        $connect = $this->database->getConnection();
        if (!(is_resource($connect) || is_object($connect))) {
            return [];
        }
        $result = ldap_search(
            $connect,
            $this->dialect->domainDn($this->database->getDomain()),
            $this->dialect->filter($this->queryBuilder)
        );
        if (false === $result) {
            return [];
        }
        $items = ldap_get_entries($connect, $result);
        return false !== $items ? $items : [];
    }

    /**
     * @param string[] $params
     * @throws MapperException
     * @return bool
     */
    public function authorize(array $params): bool
    {
        if (empty($params['user'])) {
            throw new MapperException('Cannot determine user!');
        }
        if (empty($params['password'])) {
            throw new MapperException('Password not set!');
        }
        $this->database->disconnect();
        $this->database->connect(false);
        $connect = $this->database->getConnection();
        if (!(is_resource($connect) || is_object($connect))) {
            return false;
        }
        $result = ldap_bind($connect, $this->dialect->userDn($this->database->getDomain(), $params['user']), $params['password']);
        $this->database->disconnect();
        return $result;
    }
}

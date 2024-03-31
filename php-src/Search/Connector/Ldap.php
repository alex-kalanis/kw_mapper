<?php

namespace kalanis\kw_mapper\Search\Connector;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\TFill;
use kalanis\kw_mapper\Storage;


/**
 * Class Ldap
 * @package kalanis\kw_mapper\Search
 * Connect LDAP as datasource
 * Lightweight Directory Access Protocol
 * LDAP is in reality a tree. So it's similar to normal volume and its content. The key is path and value is content.
 * @codeCoverageIgnore for now - external source
 */
class Ldap extends AConnector
{
    use TFill;

    protected Storage\Database\Raw\Ldap $database;
    protected Storage\Database\Dialects\LdapQueries $dialect;

    /**
     * @param ARecord $record
     * @param Storage\Shared\QueryBuilder|null $builder
     * @throws MapperException
     */
    public function __construct(ARecord $record, ?Storage\Shared\QueryBuilder $builder = null)
    {
        $this->basicRecord = $record;
        $this->initRecordLookup($record);
        $config = Storage\Database\ConfigStorage::getInstance()->getConfig($record->getMapper()->getSource());
        $this->database = Storage\Database\DatabaseSingleton::getInstance()->getDatabase($config);
        $this->dialect = new Storage\Database\Dialects\LdapQueries();
        $this->queryBuilder = $builder ?: new Storage\Shared\QueryBuilder();
        $this->queryBuilder->setBaseTable($record->getMapper()->getAlias());
    }

    public function getCount(): int
    {
        $entries = $this->multiple();
        if (empty($entries) || empty($entries['count'])) {
            return 0;
        }
        return intval($entries['count']);
    }

    public function getResults(): array
    {
        $lines = $this->multiple();
        if (empty($lines)) {
            return [];
        }

        $result = [];
        $relationMap = array_flip($this->basicRecord->getMapper()->getRelations());
        foreach ($lines as $key => $line) {
            if (is_numeric($key) && is_iterable($line)) {
                $rec = clone $this->basicRecord;
                foreach ($line as $index => $item) {
                    $entry = $rec->getEntry(strval($relationMap[$index]));
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
     * @throws MapperException
     * @return array<string|int, string|int|array<string|int, string|int|float|null>>
     */
    protected function multiple(): array
    {
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
        return (false !== $items) ? $items : [];
    }
}

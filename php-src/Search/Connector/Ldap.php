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

    /** @var Storage\Database\Raw\Ldap */
    protected $database = null;
    /** @var Storage\Database\Dialects\LdapQueries */
    protected $dialect = null;

    /**
     * @param ARecord $record
     * @throws MapperException
     */
    public function __construct(ARecord $record)
    {
        $this->basicRecord = $record;
        $alias = $record->getMapper()->getAlias();
        $this->records[$alias] = $record;
        $this->childTree[$alias] = [$alias => $alias];
        $config = Storage\Database\ConfigStorage::getInstance()->getConfig($record->getMapper()->getSource());
        $this->database = Storage\Database\DatabaseSingleton::getInstance()->getDatabase($config);
        $this->dialect = new Storage\Database\Dialects\LdapQueries();
        $this->queryBuilder = new Storage\Shared\QueryBuilder();
        $this->queryBuilder->setBaseTable($alias);
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
            if (is_numeric($key)) {
                $rec = clone $this->basicRecord;
                foreach ($line as $index => $item) {
                    $entry = $rec->getEntry($relationMap[$index]);
                    $entry->setData($this->typedFillSelection($entry, $this->readItem($item)), true);
                }
                $result[] = $rec;
            }
        }
        return $result;
    }

    protected function readItem($item)
    {
        return (empty($item) || empty($item[0]) || ('NULL' == $item[0])) ? '' : $item[0];
    }

    /**
     * @return array
     * @throws MapperException
     */
    protected function multiple(): array
    {
        $result = ldap_search(
            $this->database->getConnection(),
            $this->dialect->domainDn($this->database->getDomain()),
            $this->dialect->filter($this->queryBuilder)
        );
        if (false === $result) {
            return [];
        }
        return ldap_get_entries($this->database->getConnection(), $result);
    }
}

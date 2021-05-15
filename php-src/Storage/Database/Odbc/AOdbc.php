<?php

namespace kalanis\kw_mapper\Storage\Database\Odbc;


use kalanis\kw_mapper\Interfaces\IPassConnection;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\ADatabase;
use kalanis\kw_mapper\Storage\Database\TBindNames;
use kalanis\kw_mapper\Storage\Database\TConnection;


/**
 * Class AOdbc
 * @package kalanis\kw_mapper\Storage\Database\Odbc
 * Open Database Connectivity as datasource
 * Connect nearly anything - MS Access, MS Excel, DB2, Solid, Sybase, Adabas D, ...
 * @link https://www.php.net/manual/en/book.uodbc.php
 * @link https://www.php.net/manual/en/intro.uodbc.php
 * @codeCoverageIgnore remote connection
 */
abstract class AOdbc extends ADatabase implements IPassConnection
{
    use TBindNames;
    use TConnection;

    /** @var resource|null */
    protected $connection = null;

    public function disconnect(): void
    {
        if ($this->isConnected()) {
            odbc_close($this->connection);
            $this->connection = null;
        }
    }

    /**
     * @param string $query
     * @param array $params
     * @return array
     * @throws MapperException
     * @link https://www.php.net/manual/en/function.odbc-prepare.php#71616
     */
    public function query(string $query, array $params): array
    {
        if (empty($query)) {
            return [];
        }

        $this->connect();

        list($updQuery, $binds, $types) = $this->bindFromNamedToQuestions($query, $params);
        $statement = odbc_prepare($this->connection, $updQuery);

        if (odbc_execute($statement, $binds)) {
            $row = [];

            if (!odbc_fetch_row($statement)) {
                return $row;
            }

            $numFields = odbc_num_fields($statement);
            for ($i=1; $i<=$numFields; $i++) {
                // odbc starts its indexes at 1 but since I am
                // trying to emulate the functionality of *_fetch_array
                // for other dbs (ie mysql)  I'm going to decrement my
                // my numeric index by 1.  This might not be what
                // you are after in which case get rid of the -1
                $row[odbc_field_name($statement, $i)] = $row[$i - 1] = odbc_result($statement, $i);
            }
            odbc_free_result($statement);
            return $row;
        } else {
            // handle error
            throw new MapperException('ODBC query error: ' . odbc_error());
        }
    }

    /**
     * @param string $query
     * @param array $params
     * @return bool
     * @throws MapperException
     */
    public function exec(string $query, array $params): bool
    {
        if (empty($query)) {
            return false;
        }

        $this->connect();

        list($updQuery, $binds, $types) = $this->bindFromNamedToQuestions($query, $params);
        $statement = odbc_prepare($this->connection, $updQuery);
        $result = odbc_execute($statement, $binds);
        odbc_free_result($statement);
        return $result;
    }

    /**
     * @throws MapperException
     */
    public function connect(): void
    {
        if (!$this->isConnected()) {
            $this->connection = $this->connectToSystem();
        }
    }

    /**
     * @return resource
     * @throws MapperException
     */
    protected function connectToSystem()
    {
        $odbc = odbc_connect($this->config->getLocation(), $this->config->getUser(), $this->config->getPassword(), $this->config->getType());
        if (false === $odbc) {
            throw new MapperException('ODBC connection error: ' . odbc_errormsg());
        }
        return $odbc;
    }
}

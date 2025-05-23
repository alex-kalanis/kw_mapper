<?php

namespace kalanis\kw_mapper\Storage\Database\PDO;


use kalanis\kw_mapper\Storage\Database\Dialects;
use PDO;


/**
 * Class MSSQL
 * @package kalanis\kw_mapper\Storage\Database\PDO
 * Connection to Microsoft SQL, they based it on TransactSQL
 * Can be also used for Sybase DB, because they have similar base
 *
 * To make driver on Linux use following pages:
 * @link https://www.php.net/manual/en/ref.pdo-sqlsrv.php
 * @link https://github.com/microsoft/msphpsql
 * @link https://learn.microsoft.com/en-us/sql/linux/quickstart-install-connect-docker?view=sql-server-ver16&tabs=cli&pivots=cs1-bash
 *
 * Also beware that MS needs explicitly state name of column for "ORDER BY" when you use "OFFSET"-"LIMIT"
 */
class MSSQL extends APDO
{
    protected string $extension = 'pdo_sqlsrv';

    public function languageDialect(): string
    {
        return Dialects\TransactSQL::class;
    }

    protected function connectToServer(): PDO
    {
        $connection = new PDO(
            sprintf('sqlsrv:server=%s;Database=%s;',
                $this->config->getLocation(),
                $this->config->getDatabase()
            ),
            $this->config->getUser(),
            $this->config->getPassword()
        );

        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($this->config->isPersistent()) {
            $connection->setAttribute(PDO::ATTR_PERSISTENT, true);
        }

        foreach ($this->attributes as $key => $value){
            $connection->setAttribute($key, $value);
        }

        return $connection;
    }
}

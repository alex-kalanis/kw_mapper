<?php

namespace kalanis\kw_mapper\Storage\Database\PDO;


use PDO;


/**
 * Class MSSQLTrusting
 * @package kalanis\kw_mapper\Storage\Database\PDO
 * Connection to Microsoft SQL, they based it on TransactSQL
 * Can be also used for Sybase DB, because they have similar base
 *
 * This extension is just to trust automatically to server certificates
 * @link https://stackoverflow.com/questions/71688125/odbc-driver-18-for-sql-serverssl-provider-error1416f086#72348333
 */
class MSSQLTrusting extends MSSQL
{
    protected function connectToServer(): PDO
    {
        $connection = new PDO(
            sprintf('sqlsrv:server=%s;Database=%s;TrustServerCertificate=yes;',
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

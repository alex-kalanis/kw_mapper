<?php

namespace kalanis\kw_mapper\Storage\Database\PDO;


use kalanis\kw_mapper\Storage\Database\Dialects;
use PDO;


/**
 * Class MSSQL
 * @package kalanis\kw_mapper\Storage\Database\PDO
 * Connection to Microsoft SQL, they based it on TransactSQL
 * Can be also used for Sybase DB, because they have similar base
 */
class MSSQL extends APDO
{
    protected string $extension = 'pdo_mssql';

    public function languageDialect(): string
    {
        return Dialects\TransactSQL::class;
    }

    protected function connectToServer(): PDO
    {
        $connection = new PDO(
            sprintf('mssql:host=%s;port=%d;dbname=%s',
                $this->config->getLocation(),
                $this->config->getPort(),
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

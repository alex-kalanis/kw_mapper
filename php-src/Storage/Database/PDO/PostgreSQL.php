<?php

namespace kalanis\kw_mapper\Storage\Database\PDO;


use kalanis\kw_mapper\Storage\Database\Dialects;
use PDO;


/**
 * Class PostgreSQL
 * @package kalanis\kw_mapper\Storage\Database\PDO
 */
class PostgreSQL extends APDO
{
    protected $extension = 'pdo_pgsql';

    public function languageDialect(): string
    {
        return Dialects\PostgreSQL::class;
    }

    protected function connectToServer(): PDO
    {
        $connection = new PDO(
            sprintf('pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s',
                $this->config->getLocation(),
                $this->config->getPort(),
                $this->config->getDatabase(),
                $this->config->getUser(),
                $this->config->getPassword()
            )
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

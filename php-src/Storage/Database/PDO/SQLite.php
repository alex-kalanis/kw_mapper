<?php

namespace kalanis\kw_mapper\Storage\Database\PDO;


use PDO;


/**
 * Class SQLite
 * @package kalanis\kw_mapper\Storage\Database
 */
class SQLite extends APDO
{
    public function languageDialect(): string
    {
        return '\kalanis\kw_mapper\Storage\Database\Dialects\SQLite';
    }

    protected function connectToServer(): PDO
    {
        $connection = new PDO('sqlite:' . $this->config->getLocation() . $this->config->getDatabase());
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        foreach ($this->attributes as $key => $value){
            $connection->setAttribute($key, $value);
        }

        $connection->exec('PRAGMA main.cache_size = 10000;');
        $connection->exec('PRAGMA main.temp_store = MEMORY;');
        $connection->exec('PRAGMA foreign_keys = ON;');
        $connection->exec('PRAGMA main.journal_mode = WAL;');

        return $connection;
    }
}

<?php

namespace kalanis\kw_mapper\Storage\Database\PDO;


use kalanis\kw_mapper\Storage\Database\Dialects;
use PDO;


/**
 * Class SQLite
 * @package kalanis\kw_mapper\Storage\Database\PDO
 */
class SQLite extends APDO
{
    protected $extension = 'pdo_sqlite';

    public function languageDialect(): string
    {
        return Dialects\SQLite::class;
    }

    protected function connectToServer(): PDO
    {
        $connection = new PDO('sqlite:' . $this->config->getLocation() . $this->config->getDatabase(), $this->config->getUser(), $this->config->getPassword());
        $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        if ($this->config->isPersistent()) {
            $connection->setAttribute(PDO::ATTR_PERSISTENT, true);
        }

        foreach ($this->attributes as $key => $value){
            $connection->setAttribute($key, $value);
        }

//        $connection->exec('PRAGMA main.cache_size = 10000;');
//        $connection->exec('PRAGMA main.temp_store = MEMORY;');
//        $connection->exec('PRAGMA foreign_keys = ON;');
//        $connection->exec('PRAGMA main.journal_mode = WAL;');

        return $connection;
    }
}

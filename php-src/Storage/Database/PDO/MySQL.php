<?php

namespace kalanis\kw_mapper\Storage\Database\PDO;


use kalanis\kw_mapper\Storage\Database\Dialects;
use PDO;


/**
 * Class MySQL
 * @package kalanis\kw_mapper\Storage\Database\PDO
 * Can be also used for Sphinx search engine
 */
class MySQL extends APDO
{
    protected string $extension = 'pdo_mysql';

    public function languageDialect(): string
    {
        return Dialects\MySQL::class;
    }

    protected function connectToServer(): PDO
    {
        ini_set('mysql.connect_timeout', strval($this->config->getTimeout()));
        ini_set('default_socket_timeout', strval($this->config->getTimeout()));

        $connection = new PDO(
            sprintf('mysql:host=%s;port=%d;dbname=%s',
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

        $connection->query('SET NAMES utf8;');

        return $connection;
    }
}

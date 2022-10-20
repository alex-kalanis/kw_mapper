<?php

namespace kalanis\kw_mapper\Storage\Database\PDO;


use kalanis\kw_mapper\Storage\Database\Dialects;
use PDO;


/**
 * Class Oracle
 * @package kalanis\kw_mapper\Storage\Database\PDO
 * @codeCoverageIgnore remote connection
 */
class Oracle extends APDO
{
    protected $extension = 'pdo_oracle';

    public function languageDialect(): string
    {
        return Dialects\Oracle::class;
    }

    protected function connectToServer(): PDO
    {
        $connection = new PDO(
            sprintf('oci:host=%s;port=%d;dbname=%s',
                $this->config->getLocation(),
                $this->config->getPort(),
                $this->config->getDatabase()
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

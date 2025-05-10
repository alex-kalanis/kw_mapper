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
    protected string $extension = 'pdo_oci';

    public function languageDialect(): string
    {
        return Dialects\Oracle::class;
    }

    protected function connectToServer(): PDO
    {
        $connection = new PDO(
            sprintf('oci:dbname=%s',
                $this->config->getLocation()
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

<?php

namespace kalanis\kw_mapper\Storage\Database\PDO;


use PDO;


/**
 * Class Oracle
 * @package kalanis\kw_mapper\Storage\Database\PDO
 * @codeCoverageIgnore remote connection
 */
class Oracle extends APDO
{
    protected $extension = 'pdo-oracle';

    public function languageDialect(): string
    {
        return '\kalanis\kw_mapper\Storage\Database\Dialects\Oracle';
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

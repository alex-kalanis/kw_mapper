<?php

namespace kalanis\kw_mapper\Storage\Database\Raw;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\ADatabase;


/**
 * Class Ldap
 * @package kalanis\kw_mapper\Storage\Database
 * Lightweight directory access protocol
 * @link https://www.php.net/manual/en/function.ldap-bind
 * @link https://www.geekshangout.com/php-example-get-data-active-directory-via-ldap/
 * @link https://github.com/etianen/django-python3-ldap/blob/master/django_python3_ldap/ldap.py
 * @link https://github.com/django-auth-ldap/django-auth-ldap/blob/master/django_auth_ldap/backend.py
 */
class Ldap extends ADatabase
{
    /** @var resource|null */
    protected $connection = null;

    public function __destruct()
    {
        if ($this->isConnected()) {
            $this->reconnect();
        }
    }

    public function languageDialect(): string
    {
        return '\kalanis\kw_mapper\Storage\Database\Dialects\Ldap';
    }

    public function reconnect(): void
    {
        if ($this->connection) {
            ldap_unbind($this->connection);
        }
        $this->connection = null;
    }

    /**
     * @param bool $withBind
     * @return resource|null
     * @throws MapperException
     */
    public function getConnection(bool $withBind = true)
    {
        if (!$this->isConnected()) {
            $this->connectToServer($withBind);
        }
        return $this->connection;
    }

    /**
     * @param bool $withBind
     * @return resource
     * @throws MapperException
     */
    protected function connectToServer(bool $withBind = true)
    {
        $connection = ldap_connect(
            $this->config->getLocation(),
            $this->config->getPort()
        );

        if (false === $connection) {
            throw new \RuntimeException('Ldap connection error.');
        }

        if ( false !== strpos($this->config->getLocation(), 'ldaps://' )) {
            if (!ldap_start_tls($connection)) {
                throw new MapperException('Cannot start TLS for secured connection!');
            }
        }
        // Go with LDAP version 3 if possible (needed for renaming and Novell schema fetching)
        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        // We need this for doing a LDAP search.
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

        if ($withBind) {
            if (!ldap_bind($connection, $this->config->getUser(), $this->config->getPassword())) {
                throw new \RuntimeException('Ldap bind failed: ' . ldap_error($connection));
            }
        }

        return $connection;
    }

    public function isConnected(): bool
    {
        return !empty($this->connection);
    }

    /**
     * @return string
     * @throws MapperException
     */
    public function getDomain(): string
    {
        if (!isset($this->attributes['domain'])) {
            throw new MapperException('The domain is not set!');
        }
        return $this->attributes['domain'];
    }
}

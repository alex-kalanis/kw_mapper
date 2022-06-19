<?php

namespace kalanis\kw_mapper\Storage\Database\Raw;


use kalanis\kw_mapper\Interfaces\IPassConnection;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\ADatabase;
use kalanis\kw_mapper\Storage\Database\TConnection;


/**
 * Class Ldap
 * @package kalanis\kw_mapper\Storage\Database\Raw
 * Lightweight directory access protocol
 * @link https://www.php.net/manual/en/function.ldap-bind
 * @link https://www.geekshangout.com/php-example-get-data-active-directory-via-ldap/
 * @link https://github.com/etianen/django-python3-ldap/blob/master/django_python3_ldap/ldap.py
 * @link https://github.com/django-auth-ldap/django-auth-ldap/blob/master/django_auth_ldap/backend.py
 * @codeCoverageIgnore remote connection
 */
class Ldap extends ADatabase implements IPassConnection
{
    use TConnection;

    protected $extension = 'ldap';
    /** @var resource|null */
    protected $connection = null;

    public function languageDialect(): string
    {
        return '\kalanis\kw_mapper\Storage\Database\Dialects\EmptyDialect';
    }

    public function disconnect(): void
    {
        if ($this->isConnected()) {
            ldap_unbind($this->connection);
        }
        $this->connection = null;
    }

    /**
     * @param bool $withBind
     * @throws MapperException
     */
    public function connect(bool $withBind = true): void
    {
        $this->connection = $this->connectToServer($withBind);
    }

    /**
     * @param bool $withBind
     * @throws MapperException
     * @return resource
     */
    protected function connectToServer(bool $withBind = true)
    {
        $connection = ldap_connect(
            $this->config->getLocation()
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

    /**
     * @throws MapperException
     * @return string
     */
    public function getDomain(): string
    {
        if (!isset($this->attributes['domain'])) {
            throw new MapperException('The domain is not set!');
        }
        return strval($this->attributes['domain']);
    }
}

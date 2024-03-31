<?php

namespace kalanis\kw_mapper\Storage\Database\Raw;


use kalanis\kw_mapper\Interfaces\IPassConnection;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\ADatabase;
use kalanis\kw_mapper\Storage\Database\Dialects;
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

    protected string $extension = 'ldap';
    /** @var resource|null */
    protected $connection = null;

    public function languageDialect(): string
    {
        return Dialects\EmptyDialect::class;
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
        if (!$this->isConnected()) {
            $this->connection = $this->connectToServer();
            if ($withBind) {
                $this->bindUser($this->config->getUser(), $this->config->getPassword());
            }
        }
    }

    /**
     * @throws MapperException
     * @return resource
     */
    protected function connectToServer()
    {
        $connection = ldap_connect($this->config->getLocation());

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

        return $connection;
    }

    /**
     * @param string $domain
     * @param array<string|int, int|string|float|null> $changed
     * @throws MapperException
     * @return bool
     */
    public function add(string $domain, array $changed): bool
    {
        return ldap_add($this->getConnection(), $domain, $changed);
    }

    /**
     * @param string $domain
     * @param array<string|int, int|string|float|null> $changed
     * @throws MapperException
     * @return bool
     */
    public function replace(string $domain, array $changed): bool
    {
        return ldap_mod_replace($this->getConnection(), $domain, $changed);
    }

    /**
     * @param string $domain
     * @throws MapperException
     * @return bool
     */
    public function delete(string $domain): bool
    {
        return ldap_delete($this->getConnection(), $domain);
    }

    /**
     * @param string $domain
     * @param string $filter
     * @throws MapperException
     * @return array<string|int, string|int|float|array<string|int|float>>
     */
    public function search(string $domain, string $filter): array
    {
        $result = ldap_search($this->getConnection(), $domain, $filter);
        if (false === $result) {
            return [];
        }

        $items = ldap_get_entries($this->getConnection(), $result);
        if (false === $items) {
            return [];
        }

        return $items;
    }

    /**
     * @param string $domain
     * @param string $pass
     * @throws MapperException
     * @return bool
     */
    public function bindUser(string $domain, string $pass): bool
    {
        return ldap_bind($this->getConnection(), $domain, $pass);
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

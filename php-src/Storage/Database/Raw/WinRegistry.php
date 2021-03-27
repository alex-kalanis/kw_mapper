<?php

namespace kalanis\kw_mapper\Storage\Database\Raw;


use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\Interfaces\IRegistry;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\ADatabase;
use kalanis\kw_mapper\Storage\Database\Config;


/**
 * Class WinRegistry
 * @package kalanis\kw_mapper\Storage\Database\Raw
 *
 * We are not crazy enough - let's work with Windows Registry! In PHP.
 * Seriously... Just the existence of this class is a pure heresy.
 * - the path is tree and there is bunch of keys
 * - the difference with registry and normal file is simple - there is also content type, not just path and content
 * -> it's similar to flags in *nix or app rights in OS9
 * @link https://www.sitepoint.com/access-the-windows-registry-from-php/
 * @link https://www.codeproject.com/Tips/418527/Registry-Key-Handling-Through-PHP
 * Dependency: win32std.dll or win32std.so
 * @link http://pecl.php.net/package/win32std
 * @link https://github.com/RDashINC/win32std
 * @codeCoverageIgnore remote connection
 */
class WinRegistry extends ADatabase
{
    protected $extension = 'win32std';

    protected static $allowedParts = [
        IRegistry::HKEY_CLASSES_ROOT,
        IRegistry::HKEY_CURRENT_CONFIG,
        IRegistry::HKEY_CURRENT_USER,
        IRegistry::HKEY_LOCAL_MACHINE,
        IRegistry::HKEY_USERS,
    ];

    protected static $allowedTypes = [
        IRegistry::REG_DWORD => REG_DWORD,
        IRegistry::REG_SZ => REG_SZ,
        IRegistry::REG_EXPAND_SZ => REG_EXPAND_SZ,
        IRegistry::REG_MULTI_SZ => REG_MULTI_SZ,
        IRegistry::REG_BINARY => REG_BINARY,
        IRegistry::REG_NONE => REG_NONE,
    ];

    public function __construct(Config $config)
    {
        if ('Windows' != PHP_OS_FAMILY) {
            throw new MapperException('You need to run this from Windows to access registry!');
        }
        parent::__construct($config);
    }

    public function languageDialect(): string
    {
        return '\kalanis\kw_mapper\Storage\Database\Dialects\EmptyDialect';
    }

    public function reconnect(): void
    {
    }

    /**
     * @param int $part
     * @param string $key
     * @return string[][]
     * @throws MapperException
     */
    public function values(int $part, string $key): array
    {
        if (empty($key)) {
            return [];
        }

        if (!in_array($part, static::$allowedParts)) {
            throw new MapperException('You must set correct part of registry tree!');
        }

        $resource = @reg_open_key($part, $key);
        if (empty($resource)) {
            throw new MapperException(sprintf('Cannot access registry key *%s*', $key));
        }

        $results = [];

        $values = reg_enum_value($resource);
        foreach ($values as $index => $value) {
            $results[$index] = [$value, reg_get_value($resource, $value)];
        }

        @reg_close_key($resource);
        return $results;
    }

    /**
     * @param int $part
     * @param string $key
     * @return string[][]
     * @throws MapperException
     */
    public function subtree(int $part, string $key): array
    {
        if (empty($key)) {
            return [];
        }

        if (!in_array($part, static::$allowedParts)) {
            throw new MapperException('You must set correct part of registry tree!');
        }

        $resource = @reg_open_key($part, $key);
        if (empty($resource)) {
            throw new MapperException(sprintf('Cannot access registry key *%s*', $key));
        }

        $subKeys = reg_enum_key($resource);

        @reg_close_key($resource);
        return $subKeys;
    }

    /**
     * @param string $action
     * @param int $part
     * @param string $key
     * @param int $type content type flag
     * @param mixed $content content itself
     * @return bool
     * @throws MapperException
     */
    public function exec(string $action, int $part, string $key, int $type = REG_NONE, $content = ''): bool
    {
        if (empty($key)) {
            return false;
        }

        if (!in_array($part, static::$allowedParts)) {
            throw new MapperException('You must set correct part of registry tree!');
        }

        if (!isset(static::$allowedTypes[$type])) {
            throw new MapperException(sprintf('Problematic type *%s*', strval($key)));
        }

        $resource = @reg_open_key($part, $key);
        if (empty($resource)) {
            throw new MapperException(sprintf('Cannot access registry key *%s*', $key));
        }

        if (IDriverSources::ACTION_INSERT == $action) {
            reg_set_value($resource, static::$allowedTypes[$type], $content);
        } elseif (IDriverSources::ACTION_UPDATE == $action) {
            reg_set_value($resource, static::$allowedTypes[$type], $content);
        } elseif (IDriverSources::ACTION_DELETE == $action) {
            throw new MapperException('Are your really want to delete data from Registry?');
        } else {
            @reg_close_key($resource);
            return false;
        }

        @reg_close_key($resource);
        return true;
    }

    public function isConnected(): bool
    {
        throw new MapperException('The connection is only available per key');
    }
}

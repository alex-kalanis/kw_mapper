<?php

namespace kalanis\kw_mapper\Storage\Database\Raw;


use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\Interfaces\IRegistry;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\ADatabase;
use kalanis\kw_mapper\Storage\Database\Dialects;
use kalanis\kw_mapper\Storage\Database\Config;


/**
 * Class WinRegistry
 * @package kalanis\kw_mapper\Storage\Database\Raw
 *
 * We are not crazy enough - let's work with Windows Registry! In PHP.
 * Seriously... Just the existence of this class is a pure heresy.
 * @link https://www.sitepoint.com/access-the-windows-registry-from-php/
 * Dependency: win32std.dll or win32std.so
 * @link http://pecl.php.net/package/win32std
 * @link https://github.com/RDashINC/win32std
 * @codeCoverageIgnore remote connection
 */
class WinRegistry extends ADatabase
{
    protected string $extension = 'win32std';

    /** @var int[] */
    protected static $allowedParts = [
        IRegistry::HKEY_CLASSES_ROOT,
        IRegistry::HKEY_CURRENT_CONFIG,
        IRegistry::HKEY_CURRENT_USER,
        IRegistry::HKEY_LOCAL_MACHINE,
        IRegistry::HKEY_USERS,
    ];

    /** @var array<string, int> */
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
        return Dialects\EmptyDialect::class;
    }

    /**
     * @param int $part
     * @param string $key
     * @throws MapperException
     * @return string[][]
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
     * @throws MapperException
     * @return string[][]
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
     * @param string $type content type flag
     * @param mixed $content content itself
     * @throws MapperException
     * @return bool
     */
    public function exec(string $action, int $part, string $key, string $type = IRegistry::REG_NONE, $content = ''): bool
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
        @reg_close_key($resource);

        if (IDriverSources::ACTION_INSERT == $action) {
            reg_set_value($part, $key, static::$allowedTypes[$type], $content);
        } elseif (IDriverSources::ACTION_UPDATE == $action) {
            reg_set_value($part, $key, static::$allowedTypes[$type], $content);
        } elseif (IDriverSources::ACTION_DELETE == $action) {
            throw new MapperException('Are your really want to delete data from Registry?');
        } else {
            return false;
        }

        return true;
    }
}

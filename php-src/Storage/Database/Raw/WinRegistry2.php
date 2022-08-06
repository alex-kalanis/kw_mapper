<?php

namespace kalanis\kw_mapper\Storage\Database\Raw;


use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\Interfaces\IPassConnection;
use kalanis\kw_mapper\Interfaces\IRegistry;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\ADatabase;
use kalanis\kw_mapper\Storage\Database\Config;
use kalanis\kw_mapper\Storage\Database\TConnection;
use kalanis\kw_mapper\Storage\Shared\DotNet\ComRegistry;


/**
 * Class WinRegistry2
 * @package kalanis\kw_mapper\Storage\Database\Raw
 *
 * We are not crazy enough - let's work with Windows Registry! In PHP.
 * Seriously... Just the existence of this class is a pure heresy.
 * @todo: anyone knows how to return list of keys from tree node?
 * @todo: anyone knows how to also return type of entry?
 *
 * @link https://www.codeproject.com/Tips/418527/Registry-Key-Handling-Through-PHP
 * Dependency: com_dotnet.dll or com_dotnet.so
 * @codeCoverageIgnore remote connection
 */
class WinRegistry2 extends ADatabase implements IPassConnection
{
    use TConnection;

    protected $extension = 'com_dotnet';
    /** @var ComRegistry|null */
    protected $connection = null;

    /** @var array<int, string> */
    protected static $allowedParts = [
        IRegistry::HKEY_CLASSES_ROOT => 'HKCR',
        IRegistry::HKEY_CURRENT_CONFIG => 'HKEY_CURRENT_CONFIG',
        IRegistry::HKEY_CURRENT_USER => 'HKCU',
        IRegistry::HKEY_LOCAL_MACHINE => 'HKLM',
        IRegistry::HKEY_USERS => 'HKEY_USERS',
    ];

    /** @var array<string, string> */
    protected static $allowedTypes = [
        IRegistry::REG_DWORD => 'REG_DWORD',
        IRegistry::REG_SZ => 'REG_SZ',
        IRegistry::REG_EXPAND_SZ => 'REG_EXPAND_SZ',
//        IRegistry::REG_MULTI_SZ => 'REG_MULTI_SZ',
        IRegistry::REG_BINARY => 'REG_BINARY',
        IRegistry::REG_NONE => 'REG_NONE',
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

    /**
     * @param int $part
     * @param string $key  beware of tailing slashes!
     * @throws MapperException
     * @return mixed
     */
    public function value(int $part, string $key)
    {
        if (empty($key)) {
            return [];
        }

        if (!isset(static::$allowedParts[$part])) {
            throw new MapperException('You must set correct part of registry tree!');
        }

        $this->connect();

        try {
            return $this->connection->RegRead(sprintf('%s\\%s', static::$allowedParts[$part], $key));
        } catch (\Exception $ex) {
            throw new MapperException(sprintf('Cannot access registry key *%s*', $key), 0, $ex);
        }
    }

//    /**
//     * @param int $part
//     * @param string $key
//     * @return string[][]
//     * @throws MapperException
//     */
//    public function subtree(int $part, string $key): array
//    {
//        if (empty($key)) {
//            return [];
//        }
//
//        if (!in_array($part, array_keys(static::$allowedParts))) {
//            throw new MapperException('You must set correct part of registry tree!');
//        }
//
//        $this->connect();
//
//        // ends with slash - got tree list
//        $resource = $this->connection->RegRead(sprintf('%s\\%s\\', static::$allowedParts[$part], $this->dropSlash($key)));
//
//        if (empty($resource)) {
//            throw new MapperException(sprintf('Cannot access registry key *%s*', $key));
//        }
//
//        return $resource;
//    }

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

        if (!isset(static::$allowedParts[$part])) {
            throw new MapperException('You must set correct part of registry tree!');
        }

        if (!isset(static::$allowedTypes[$type])) {
            throw new MapperException(sprintf('Problematic type *%s*', strval($key)));
        }

        $this->connect();

        // ends without slash - got value
        $path = sprintf('%s\\%s', static::$allowedParts[$part], $this->dropSlash($key));
        $path .= (IRegistry::REG_NONE == $type) ? '\\' : ''; // none type = it's "dir", not "entry"

        if (IDriverSources::ACTION_INSERT == $action) {
            try{
                $this->connection->RegWrite($path, $content, static::$allowedTypes[$type]);
            } catch (\Exception $e) {
                throw new MapperException('Cannot write into registry', 0, $e);
            }
        } elseif (IDriverSources::ACTION_UPDATE == $action) {
            try{
                $this->connection->RegWrite($path, $content, static::$allowedTypes[$type]);
            } catch (\Exception $e) {
                throw new MapperException('Cannot write into registry', 0, $e);
            }
        } elseif (IDriverSources::ACTION_DELETE == $action) {
            try{
                $this->connection->RegDelete($path);
            } catch(\Exception $e) {
                throw new MapperException('Cannot delete from registry', 0, $e);
            }
        } else {
            return false;
        }

        return true;
    }

    public function connect(): void
    {
        if (!$this->isConnected()) {
            $this->connection = new ComRegistry();
        }
    }

    protected function dropSlash(string $key): string
    {
        return ('\\' == mb_substr($key, -1, 1)) ? mb_substr($key, 0, -1) : $key ;
    }
}

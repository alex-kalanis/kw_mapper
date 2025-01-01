<?php

use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\AMapper;
use kalanis\kw_mapper\Mappers\Database\ADatabase;
use kalanis\kw_mapper\Mappers\File;
use kalanis\kw_mapper\Mappers\Storage;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_mapper\Storage\Database;
use kalanis\kw_mapper\Storage\Shared;
use PHPUnit\Framework\TestCase;


/**
 * Class CommonTestClass
 * The structure for mocking and configuration seems so complicated, but it's necessary to let it be totally idiot-proof
 */
class CommonTestClass extends TestCase
{
}


class Builder extends Shared\QueryBuilder
{
    public function resetCounter(): void
    {
        $this->uniqueId->clear();
    }
}


class Builder2 extends Database\QueryBuilder
{
    public function resetCounter(): void
    {
        $this->uniqueId->clear();
    }
}


class StrObjMock
{
    public function __toString()
    {
        return 'strObj';
    }
}


/**
 * Class TableRecord
 * Source file dumped from kw_menu
 * @property string $file
 * @property int $order
 * @property string $title
 * @property string $desc
 * @property bool $sub
 */
class TableRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('file', IEntryType::TYPE_STRING, 512);
        $this->addEntry('order', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('title', IEntryType::TYPE_STRING, 512);
        $this->addEntry('desc', IEntryType::TYPE_STRING, 512);
        $this->addEntry('sub', IEntryType::TYPE_BOOLEAN);
        $this->setMapper(TableMapper::class);
    }
}


class TableMapper extends Storage\ATable
{
    protected function setMap(): void
    {
        $this->setStorage();
        $this->setFormat(Shared\FormatFiles\SeparatedElements::class);
        $this->setSource('target.meta');
        $this->setRelation('file', 0);
        $this->setRelation('order', 1);
        $this->setRelation('title', 2);
        $this->setRelation('desc', 3);
        $this->setRelation('sub', 4);
        $this->addPrimaryKey('file');
    }
}


/**
 * Class TableRecord
 * Source file dumped from kw_menu
 * @property int $id
 * @property string $file
 * @property string $title
 * @property string $desc
 * @property bool $enabled
 * @property string $param
 */
class TableIdRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('file', IEntryType::TYPE_STRING, 512);
        $this->addEntry('title', IEntryType::TYPE_STRING, 512);
        $this->addEntry('desc', IEntryType::TYPE_STRING, 512);
        $this->addEntry('enabled', IEntryType::TYPE_BOOLEAN);
        $this->addEntry('param', IEntryType::TYPE_STRING, 512);
    }

    /**
     * @throws MapperException
     */
    public function useIdAsMapper(): void
    {
        $this->setMapper(TableIdMapper::class);
    }

    /**
     * @throws MapperException
     */
    public function useNoKeyMapper(): void
    {
        $this->setMapper(TableNoPkMapper::class);
    }

    /**
     * @param string $name
     * @throws MapperException
     */
    public function setAnotherMapper(string $name): void
    {
        $this->setMapper($name);
    }
}


class TableNoPkMapper extends Storage\ATable
{
    protected function setMap(): void
    {
        $this->setStorage();
        $this->setFormat(Shared\FormatFiles\SeparatedElements::class);
        $this->setSource('target.data');
        $this->setRelation('id', 0);
        $this->setRelation('file', 1);
        $this->setRelation('title', 2);
        $this->setRelation('desc', 3);
        $this->setRelation('enabled', 4);
        $this->setRelation('param', 5);
    }
}


class TableIdMapper extends TableNoPkMapper
{
    protected function setMap(): void
    {
        parent::setMap();
        $this->addPrimaryKey('id');
    }
}


/**
 * Class XSimpleRecord
 * Simple record for testing factory
 * @property int $id
 * @property string $title
 * @property string|null $desc
 */
class XSimpleRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 64);
        $this->addEntry('title', IEntryType::TYPE_STRING, 512);
        $this->addEntry('desc', IEntryType::TYPE_STRING, 512);
    }

    /**
     * @throws MapperException
     */
    public function useDatabase(): void
    {
        $this->setMapper(XDatabaseMapper::class);
    }

    /**
     * @throws MapperException
     */
    public function useStorage(): void
    {
        $this->setMapper(XStorageMapper::class);
    }

    /**
     * @throws MapperException
     */
    public function useFile(): void
    {
        $this->setMapper(XFileMapper::class);
    }

    /**
     * @throws MapperException
     */
    public function useMock(): void
    {
        $this->setMapper(XMockMapper::class);
    }
}


class XDatabaseMapper extends ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('dummy');
        $this->setRelation('id', 'id');
        $this->setRelation('title', 'title');
        $this->setRelation('desc', 'desc');
        $this->addPrimaryKey('id');
    }
}


class XStorageMapper extends Storage\ATable
{
    protected function setMap(): void
    {
        $this->setStorage();
        $this->setFormat(Shared\FormatFiles\SeparatedElements::class);
        $this->setSource('testing_one.txt');
        $this->setRelation('id', 'id');
        $this->setRelation('title', 'title');
        $this->setRelation('desc', 'desc');
        $this->addPrimaryKey('id');
    }
}


class XFileMapper extends File\ATable
{
    protected function setMap(): void
    {
        $this->setFormat(Shared\FormatFiles\SeparatedElements::class);
        $this->setFileAccessor(new \kalanis\kw_files\Processing\Volume\ProcessFile());
        $this->setSource('dummy');
        $this->setRelation('id', 'id');
        $this->setRelation('title', 'title');
        $this->setRelation('desc', 'desc');
        $this->addPrimaryKey('id');
    }
}


class XMockMapper extends AMapper
{
    protected function setMap(): void
    {
        $this->setSource('dummy');
        $this->setRelation('id', 'id');
        $this->setRelation('title', 'title');
        $this->setRelation('desc', 'desc');
    }

    public function getAlias(): string
    {
        return $this->getSource();
    }

    protected function insertRecord(ARecord $record): bool
    {
        return false;
    }

    protected function updateRecord(ARecord $record): bool
    {
        return false;
    }

    public function countRecord(ARecord $record): int
    {
        return 0;
    }

    public function loadMultiple(ARecord $record): array
    {
        return [];
    }

    protected function loadRecord(ARecord $record): bool
    {
        return false;
    }

    protected function deleteRecord(ARecord $record): bool
    {
        return false;
    }
}

// just fallback for MS Win system - usually defined in external library

if (!defined('REG_DWORD')) {
    define('REG_DWORD', 1);
}
if (!defined('REG_SZ')) {
    define('REG_SZ', 5);
}
if (!defined('REG_EXPAND_SZ')) {
    define('REG_EXPAND_SZ', 2);
}
if (!defined('REG_MULTI_SZ')) {
    define('REG_MULTI_SZ', 3);
}
if (!defined('REG_BINARY')) {
    define('REG_BINARY', 0);
}
if (!defined('REG_NONE')) {
    define('REG_NONE', 4);
}


if (!function_exists('reg_close_key')) {
    /**
     * @param resource $resource
     * @throws MapperException
     * @return void
     */
    function reg_close_key($resource) {
        throw new MapperException('No win32std library!');
    }
}

if (!function_exists('reg_create_key')) {
    /**
     * @param int $part
     * @param string $subKey
     * @param int|null $samDesired
     * @throws MapperException
     * @return mixed
     */
    function reg_create_key($part, $subKey, $samDesired = null)
    {
        throw new MapperException('No win32std library!');
    }
}

if (!function_exists('reg_enum_key')) {
    /**
     * @param int $part
     * @param int $index
     * @throws MapperException
     * @return mixed
     */
    function reg_enum_key($part, $index = -1) {
        throw new MapperException('No win32std library!');
    }
}

if (!function_exists('reg_enum_value')) {
    /**
     * @param resource $resource
     * @param int $index
     * @throws MapperException
     * @return mixed
     */
    function reg_enum_value($resource, $index = -1) {
        throw new MapperException('No win32std library!');
    }
}

if (!function_exists('reg_open_key')) {
    /**
     * @param int $part
     * @param string $subKey
     * @param int|null $samDesired
     * @throws MapperException
     * @return mixed
     */
    function reg_open_key($part, $subKey, $samDesired = null) {
        throw new MapperException('No win32std library!');
    }
}

if (!function_exists('reg_set_value')) {
    /**
     * @param int $part
     * @param string $valueName
     * @param int $typeId
     * @param mixed $value
     * @throws MapperException
     * @return void
     */
    function reg_set_value($part, $valueName, $typeId, $value) {
        throw new MapperException('No win32std library!');
    }
}

if (!function_exists('reg_get_value')) {
    /**
     * @param int $part
     * @param string $valueName
     * @throws MapperException
     * @return mixed
     */
    function reg_get_value($part, $valueName) {
        throw new MapperException('No win32std library!');
    }
}

if (!class_exists('COM')) {
    /**
     * Class COM
     * Dummy class replacing COM class on systems which does not have it
     */
    class COM
    {
        /**
         * COM constructor.
         * @param string $module_name
         * @param string|null $server_name
         * @param string|null $codepage
         * @param string|null $typelib
         */
        public function __construct ( $module_name, $server_name, $codepage, $typelib ) {}

        /**
         * @param string $name
         * @throws MapperException
         * @return mixed
         */
        public function __get ($name) {
            throw new MapperException('No win32.net library!');
        }

        /**
         * @param string $name
         * @param mixed $value
         * @throws MapperException
         */
        public function __set ($name, $value) {
            throw new MapperException('No win32.net library!');
        }

        /**
         * @param string $name
         * @param array<string, mixed> $args
         * @throws MapperException
         */
        public function __call ($name, $args) {
            throw new MapperException('No win32.net library!');
        }
    }
}

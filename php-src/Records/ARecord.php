<?php

namespace kalanis\kw_mapper\Records;


use ArrayAccess;
use Iterator;
use kalanis\kw_mapper\Interfaces\ICanFill;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;


/**
 * Class ARecord
 * @package kalanis\kw_mapper\Records
 * Class to map entries to their respective values
 * The level of "obstruction" to accessing properties is necessary
 * or it could not be possible to guarantee content values.
 * The children must stay too simple to avoid some usual problems which came with multilevel extending
 */
abstract class ARecord implements ArrayAccess, Iterator
{
    use TMapper;

    private $key = null;
    /** @var Entry[] */
    protected $entries = [];

    protected static $types = [IEntryType::TYPE_BOOLEAN, IEntryType::TYPE_INTEGER, IEntryType::TYPE_FLOAT, IEntryType::TYPE_STRING, IEntryType::TYPE_SET, IEntryType::TYPE_ARRAY, IEntryType::TYPE_OBJECT];

    /**
     * Mapper constructor.
     * @throws MapperException
     */
    public function __construct()
    {
        $this->addEntries();
    }

    /**
     * @throws MapperException
     */
    abstract protected function addEntries(): void;

    /**
     * @param string $name
     * @param int $type
     * @param null $defaultParam
     * @throws MapperException
     */
    final protected function addEntry($name, int $type, $defaultParam = null)
    {
        $this->checkDefault($type, $defaultParam);
        $this->entries[$name] = Entry::getInstance()->setType($type)->setParams($defaultParam);
    }

    /**
     * @param iterable $data
     */
    public function loadWithData(iterable $data): void
    {
        foreach ($data as $key => $value) {
            if ($this->offsetExists($key)) {
                $this->offsetSet($key, $value);
            }
        }
    }

    /**
     * @param string $name
     * @return Entry
     * @throws MapperException
     */
    final public function getEntry($name): Entry
    {
        $this->offsetCheck($name);
        return $this->entries[$name];
    }

    final public function __clone()
    {
        $entries = [];
        foreach ($this->entries as $key => $entry) {
            $entries[$key] = clone $entry;
        }
        $this->entries = $entries;
    }

    /**
     * @param string|int $name
     * @param mixed $value
     */
    final public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    /**
     * @param string|int $name
     * @return int|ICanFill|string|null
     * @throws MapperException
     */
    final public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * @return int|ICanFill|string|null
     * @throws MapperException
     */
    final public function current()
    {
        return $this->valid() ? $this->offsetGet($this->key) : null ;
    }

    final public function next()
    {
        next($this->entries);
        $this->key = key($this->entries);
    }

    final public function key()
    {
        return $this->key;
    }

    final public function valid()
    {
        return $this->offsetExists($this->key);
    }

    final public function rewind()
    {
        reset($this->entries);
        $this->key = key($this->entries);
    }

    final public function offsetExists($offset)
    {
        return isset($this->entries[$offset]);
    }

    /**
     * @param mixed $offset
     * @return int|ICanFill|mixed|string|null
     * @throws MapperException
     */
    final public function offsetGet($offset)
    {
        $this->offsetCheck($offset);
        $data = & $this->entries[$offset];

        switch ($data->getType()) {
            case IEntryType::TYPE_BOOLEAN:
            case IEntryType::TYPE_INTEGER:
            case IEntryType::TYPE_FLOAT:
            case IEntryType::TYPE_STRING:
            case IEntryType::TYPE_SET:
            case IEntryType::TYPE_ARRAY:
                return $data->getData();
            case IEntryType::TYPE_OBJECT:
                if (empty($data->getData())) {
                    return null;
                }
                return $data->getData()->dumpData();
            default:
                // @codeCoverageIgnoreStart
                // happens only when someone is evil enough and change type directly on entry
                throw new MapperException(sprintf('Unknown type *%d*', $data->getType()));
                // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @param mixed $offset
     * @throws MapperException
     */
    final public function offsetUnset($offset)
    {
        throw new MapperException(sprintf('Key %s removal denied', $offset));
    }

    /**
     * @param $offset
     * @throws MapperException
     */
    final protected function offsetCheck($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new MapperException(sprintf('Unknown key *%s*', $offset));
        }
    }

    final protected function reloadClass(Entry $data)
    {
        if (empty($data->getData())) {
            $dataClass = $data->getParams();
            $classInstance = new $dataClass;
            $data->setData($classInstance);
        }
    }

    /**
     * @param int $type
     * @param $default
     * @throws MapperException
     */
    private function checkDefault(int $type, $default)
    {
        switch ($type) {
            case IEntryType::TYPE_INTEGER:
            case IEntryType::TYPE_FLOAT:
            case IEntryType::TYPE_STRING:
                $this->checkLengthNumeric($default, $type);
                return;
            case IEntryType::TYPE_BOOLEAN:
            case IEntryType::TYPE_ARRAY:
            case IEntryType::TYPE_SET:
                return;
            case IEntryType::TYPE_OBJECT:
                $this->checkObjectString($default, $type);
                $this->checkObjectInstance($default, $type);
                return;
            default:
                throw new MapperException(sprintf('Unknown type *%d*', $type));
        }
    }

    /**
     * @param mixed $value
     * @param int $type
     * @throws MapperException
     */
    private function checkLengthNumeric($value, int $type)
    {
        if (!is_numeric($value)) {
            throw new MapperException(sprintf('You must set length as number for type *%d*', $type));
        }
    }

    /**
     * @param mixed $value
     * @param int $type
     * @throws MapperException
     */
    private function checkObjectString($value, int $type)
    {
        if (!is_string($value)) {
            throw new MapperException(sprintf('You must set available string representing object for type *%d*', $type));
        }
    }

    /**
     * @param mixed $value
     * @param int $type
     * @throws MapperException
     */
    private function checkObjectInstance($value, int $type)
    {
        $classForTest = new $value();
        if (!$classForTest instanceof ICanFill) {
            throw new MapperException(sprintf('When you set string representing object for type *%d*, it must be stdClass or have ICanFill interface', $type));
        }
    }

    /**
     * From trait TMapper - map this record as one to processing
     * @return ARecord
     */
    final protected function getSelf(): ARecord
    {
        return $this;
    }
}

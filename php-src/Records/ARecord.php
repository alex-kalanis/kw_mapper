<?php

namespace kalanis\kw_mapper\Records;


use ArrayAccess;
use Iterator;
use kalanis\kw_mapper\Interfaces\ICanFill;
use kalanis\kw_mapper\Interfaces\IType;
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

    /** @var Entry[] */
    private $entries = [];
    private $key = null;

    protected static $types = [IType::TYPE_BOOLEAN, IType::TYPE_INTEGER, IType::TYPE_STRING, IType::TYPE_ARRAY, IType::TYPE_OBJECT];

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
     * @throws MapperException
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
            case IType::TYPE_BOOLEAN:
            case IType::TYPE_INTEGER:
            case IType::TYPE_STRING:
            case IType::TYPE_ARRAY:
                return $data->getData();
            case IType::TYPE_OBJECT:
                if (empty($data->getData())) {
                    return null;
                }
                return $data->getData()->dumpData();
            default:
                // @codeCoverageIgnoreStart
                // happens only when someone is evil enough and change type directly on entry
                throw new MapperException(sprintf('Unknown type %d', $data->getType()));
                // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws MapperException
     */
    final public function offsetSet($offset, $value)
    {
        $this->offsetCheck($offset);
        $data = & $this->entries[$offset];
        switch ($data->getType()) {
            case IType::TYPE_BOOLEAN:
                $this->checkBool($value, $offset);
                break;
            case IType::TYPE_INTEGER:
                $this->checkNumeric($value, $offset);
                $this->checkSize($value, intval($data->getParams()));
                break;
            case IType::TYPE_STRING:
                $this->checkString($value, $offset);
                $this->checkLength($value, intval($data->getParams()));
                break;
            case IType::TYPE_ARRAY:
                $this->checkArrayForNotEntries($value, $offset);
                break;
            case IType::TYPE_OBJECT:
                $this->reloadClass($data);
                $class = $data->getData();
                $class->fillData($value);
                return; // fill data elsewhere
            default:
                // @codeCoverageIgnoreStart
                // happens only when someone is evil enough and change type directly on entry
                throw new MapperException(sprintf('Unknown type %d', $data->getType()));
                // @codeCoverageIgnoreEnd
        }
        $data->setData($value);
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
    final private function offsetCheck($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new MapperException(sprintf('Unknown key %s', $offset));
        }
    }

    final private function reloadClass(Entry $data)
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
    final private function checkDefault(int $type, $default)
    {
        switch ($type) {
            case IType::TYPE_INTEGER:
            case IType::TYPE_STRING:
                $this->checkLengthNumeric($default, $type);
                return;
            case IType::TYPE_BOOLEAN:
            case IType::TYPE_ARRAY:
                return;
            case IType::TYPE_OBJECT:
                $this->checkObjectString($default, $type);
                $this->checkObjectInstance($default, $type);
                return;
            default:
                throw new MapperException(sprintf('Unknown type %d', $type));
        }
    }

    /**
     * @param mixed $value
     * @param string $key
     * @throws MapperException
     */
    final private function checkBool($value, string $key)
    {
        if (is_null($value)) {
            return;
        }
        if (!is_bool($value)) {
            throw new MapperException(sprintf('Try to set something other than number into key %s', $key));
        }
    }

    /**
     * @param mixed $value
     * @param string $key
     * @throws MapperException
     */
    final private function checkNumeric($value, string $key)
    {
        if (is_null($value)) {
            return;
        }
        if (!is_numeric($value)) {
            throw new MapperException(sprintf('Try to set something other than number into key %s', $key));
        }
    }

    /**
     * @param mixed $value
     * @param string $key
     * @throws MapperException
     */
    final private function checkString($value, string $key)
    {
        if (is_null($value)) {
            return;
        }
        if (!is_string($value)) {
            throw new MapperException(sprintf('Try to set something other than string into key %s', $key));
        }
    }

    /**
     * @param mixed $value
     * @param int $limit
     * @throws MapperException
     */
    final private function checkSize($value, int $limit)
    {
        if (is_null($value)) {
            return;
        }
        if ($value > $limit) {
            throw new MapperException(sprintf('Try to set number larger than allowed size (%d > %d)', $value, $limit));
        }
    }

    /**
     * @param mixed $value
     * @param int $limit
     * @throws MapperException
     */
    final private function checkLength($value, int $limit)
    {
        if (is_null($value)) {
            return;
        }
        $size = mb_strlen($value);
        if ($size > $limit) {
            throw new MapperException(sprintf('Try to set string longer than allowed size (%d > %d)', $size, $limit));
        }
    }

    /**
     * @param mixed $value
     * @param string $key
     * @throws MapperException
     */
    final private function checkArrayForNotEntries($value, string $key)
    {
        if (!is_array($value)) {
            throw new MapperException(sprintf('You must set array into key %s', $key));
        }
        foreach ($value as $item) {
            if (!$item instanceof ARecord) {
                throw new MapperException(sprintf('Array in key %s contains something that is not link to another mapper', $key));
            }
        }
    }

    /**
     * @param mixed $value
     * @param int $type
     * @throws MapperException
     */
    final private function checkLengthNumeric($value, int $type)
    {
        if (!is_numeric($value)) {
            throw new MapperException(sprintf('You must set length as number for type %d', $type));
        }
    }

    /**
     * @param mixed $value
     * @param int $type
     * @throws MapperException
     */
    final private function checkObjectString($value, int $type)
    {
        if (!is_string($value)) {
            throw new MapperException(sprintf('You must set available string representing object for type %d', $type));
        }
    }

    /**
     * @param mixed $value
     * @param int $type
     * @throws MapperException
     */
    final private function checkObjectInstance($value, int $type)
    {
        $classForTest = new $value();
        if (!$classForTest instanceof ICanFill) {
            throw new MapperException(sprintf('When you set string representing object for type %d, it must be stdClass or have ICanFill interface', $type));
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

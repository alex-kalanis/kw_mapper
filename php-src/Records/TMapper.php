<?php

namespace kalanis\kw_mapper\Records;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;


/**
 * trait TMapper
 * @package kalanis\kw_mapper\Records
 * Class to map entries to their respective values
 * The level of "obstruction" to accessing properties is necessary
 * or it could not be possible to guarantee content values.
 * The children must stay too simple to avoid some usual problems which came with multilevel extending
 */
trait TMapper
{
    /** @var Mappers\AMapper|null */
    private $mapper = null;

    /**
     * @param string $name
     * @throws MapperException
     */
    final protected function setMapper(string $name)
    {
        $this->mapper = $this->mapperFromFactory($name);
    }

    /**
     * @param string $name
     * @return Mappers\AMapper
     * @throws MapperException
     */
    protected function mapperFromFactory(string $name): Mappers\AMapper
    {
        // factory returns class as static instance, so it is not necessary to fill more memory with necessities
        return Mappers\Factory::getInstance($name);
    }

    /**
     * @throws MapperException
     */
    final private function checkMapper()
    {
        if (empty($this->mapper)) {
            throw new MapperException('Unknown entry mapper');
        }
    }

    /**
     * @param bool $forceInsert
     * @return bool
     * @throws MapperException
     */
    final public function save(bool $forceInsert = false): bool
    {
        $this->checkMapper();
        return $this->mapper->save($this->getSelf(), $forceInsert);
    }

    /**
     * @return bool
     * @throws MapperException
     */
    final public function load(): bool
    {
        $this->checkMapper();
        return $this->mapper->load($this->getSelf());
    }

    abstract protected function getSelf(): ARecord;
}

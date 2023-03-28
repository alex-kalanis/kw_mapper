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
    protected $mapper = null;
    /** @var Mappers\Factory|null */
    private $mapperFactory = null;

    /**
     * @param string $name
     * @throws MapperException
     */
    final protected function setMapper(string $name): void
    {
        $this->mapper = $this->mapperFromFactory($name);
    }

    /**
     * @param string $name
     * @throws MapperException
     * @return Mappers\AMapper
     */
    protected function mapperFromFactory(string $name): Mappers\AMapper
    {
        // factory returns class as static instance, so it is not necessary to fill more memory with necessities
        if (empty($this->mapperFactory)) {
            $this->mapperFactory = $this->mapperFactory();
        }
        return $this->mapperFactory->getInstance($name);
    }

    /**
     * You can set own factory to load other mappers
     * @return Mappers\Factory
     */
    protected function mapperFactory(): Mappers\Factory
    {
        return new Mappers\Factory();
    }

    /**
     * @param bool $forceInsert
     * @throws MapperException
     * @return bool
     */
    final public function save(bool $forceInsert = false): bool
    {
        return $this->getMapper()->/** @scrutinizer ignore-call */save($this->getSelf(), $forceInsert);
    }

    /**
     * @throws MapperException
     * @return bool
     */
    final public function load(): bool
    {
        return $this->getMapper()->/** @scrutinizer ignore-call */load($this->getSelf());
    }

    /**
     * @throws MapperException
     * @return bool
     */
    final public function delete(): bool
    {
        return $this->getMapper()->delete($this->getSelf());
    }

    /**
     * @throws MapperException
     * @return int
     */
    final public function count(): int
    {
        return $this->getMapper()->countRecord($this->getSelf());
    }

    /**
     * @throws MapperException
     * @return ARecord[]
     */
    final public function loadMultiple(): array
    {
        return $this->getMapper()->loadMultiple($this->getSelf());
    }

    /**
     * @throws MapperException
     * @return Mappers\AMapper
     */
    public function getMapper(): Mappers\AMapper
    {
        if (empty($this->mapper)) {
            throw new MapperException('Unknown entry mapper');
        }
        return $this->mapper;
    }

    abstract protected function getSelf(): ARecord;
}

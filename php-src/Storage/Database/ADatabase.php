<?php

namespace kalanis\kw_mapper\Storage\Database;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Shared\TCheckExt;


/**
 * Class ADatabase
 * @package kalanis\kw_mapper\Storage\Database
 * Dummy connector to any database which implements following requirements
 */
abstract class ADatabase
{
    use TCheckExt;

    /** @var Config */
    protected $config = null;
    /** @var string[]|int[] */
    protected $attributes = [];
    /** @var string */
    protected $extension = 'none';

    /**
     * @param Config $config
     * @throws MapperException
     */
    public function __construct(Config $config)
    {
        $this->checkExtension($this->extension);
        $this->config = $config;
    }

    /**
     * Add another attributes which will be set after db connection
     * @param $attribute
     * @param $value
     */
    public function addAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    /**
     * Returns string representation of language dialect class for query builder
     * @return string
     */
    abstract public function languageDialect(): string;

    /**
     * Check if system knows detail about connection
     * @return bool
     */
    abstract public function isConnected(): bool;

    /**
     * Reset details about connection
     */
    abstract public function reconnect(): void;
}

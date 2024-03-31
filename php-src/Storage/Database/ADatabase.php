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

    protected Config $config;
    /** @var string[]|int[] */
    protected array $attributes = [];
    protected string $extension = 'none';

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
     * @param string|int $attribute
     * @param string|int $value
     */
    public function addAttribute($attribute, $value): void
    {
        $this->attributes[$attribute] = $value;
    }

    /**
     * Returns string representation of language dialect class for query builder
     * @return string
     */
    abstract public function languageDialect(): string;
}

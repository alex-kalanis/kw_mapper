<?php

namespace kalanis\kw_mapper\Storage\Database;


use kalanis\kw_mapper\MapperException;


/**
 * Class ConfigStorage
 * @package kalanis\kw_mapper\Storage\Database
 * Singleton to access configs across the mapper system
 */
class ConfigStorage
{
    protected static ?ConfigStorage $instance = null;
    /** @var Config[] */
    private array $configs = [];

    public static function getInstance(): self
    {
        if (empty(static::$instance)) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    protected function __construct()
    {
    }

    /**
     * @codeCoverageIgnore why someone would run that?!
     */
    private function __clone()
    {
    }

    final public function addConfig(Config $config): void
    {
        $this->configs[$config->getSourceName()] = $config;
    }

    /**
     * @param string $sourceName
     * @throws MapperException
     * @return Config
     */
    final public function getConfig(string $sourceName): Config
    {
        if (empty($this->configs[$sourceName])) {
            throw new MapperException(sprintf('Unknown source *%s*', $sourceName));
        }
        return $this->configs[$sourceName];
    }
}

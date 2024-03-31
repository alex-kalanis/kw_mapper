<?php

namespace kalanis\kw_mapper\Storage\File;


/**
 * Trait TFile
 * @package kalanis\kw_mapper\Storage\File
 */
trait TFile
{
    /** @var string[] */
    protected array $presetPath = [];

    /**
     * @param string[] $path
     */
    protected function setPath(array $path): void
    {
        $this->presetPath = $path;
    }

    /**
     * @return string[]
     */
    protected function getPath(): array
    {
        return $this->presetPath;
    }
}

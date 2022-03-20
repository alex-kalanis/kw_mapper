<?php

namespace kalanis\kw_mapper\Storage\File\Formats;


use kalanis\kw_mapper\Interfaces\IFileFormat;


/**
 * Class SeparatedElements
 * @package kalanis\kw_mapper\Storage\File\Formats
 * Formats/unpack content into/from table created by separated elements in file
 */
class SeparatedElements implements IFileFormat
{
    use TNl;

    protected $delimitElements = '|';
    protected $delimitLines = PHP_EOL;

    public function setDelimiters(string $elements = '|', string $lines = PHP_EOL): self
    {
        $this->delimitElements = $elements;
        $this->delimitLines = $lines;
        return $this;
    }

    public function unpack(string $content): array
    {
        $lines = explode($this->delimitLines, $content);
        $records = [];
        foreach ($lines as &$line) {
            if (empty($line)) {
                continue;
            }

            $records[] = array_map([$this, 'unescapeNl'], explode($this->delimitElements, $line));
        }
        return $records;
    }

    public function pack(array $records): string
    {
        $lines = [];
        foreach ($records as &$record) {
            ksort($record);
            $record[] = ''; // separator on end
            $lines[] = implode($this->delimitElements, array_map([$this, 'escapeNl'], $record));
        }
        return implode($this->delimitLines, $lines);
    }
}

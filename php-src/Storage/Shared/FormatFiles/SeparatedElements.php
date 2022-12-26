<?php

namespace kalanis\kw_mapper\Storage\Shared\FormatFiles;


use kalanis\kw_mapper\Interfaces\IFileFormat;


/**
 * Class SeparatedElements
 * @package kalanis\kw_mapper\Storage\Shared\FormatFiles
 * Formats/unpack content into/from table created by separated elements in file
 */
class SeparatedElements implements IFileFormat
{
    use TNl;

    /** @var string */
    protected $delimitElements = '|';
    /** @var string */
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
        if (false !== $lines) {
            foreach ($lines as &$line) {
                if (empty($line)) {
                    continue;
                }

                $items = explode($this->delimitElements, strval($line));
                if (false !== $items) {
                    $records[] = array_map([$this, 'unescapeNl'], $items);
                }
            }
        }
        return $records;
    }

    public function pack(array $records): string
    {
        $lines = [];
        foreach ($records as &$record) {
            $record = (array) $record;
            ksort($record);
            $record[] = ''; // separator on end
            $lines[] = implode($this->delimitElements, array_map([$this, 'escapeNl'], $record));
        }
        return implode($this->delimitLines, $lines);
    }
}

<?php

namespace kalanis\kw_mapper\Storage\Shared\FormatFiles;


use kalanis\kw_mapper\Interfaces\IFileFormat;


/**
 * Class Csv
 * @package kalanis\kw_mapper\Storage\Shared\FormatFiles
 * Store it in CSV table
 */
class Csv implements IFileFormat
{
    use TNl;

    protected string $delimitLines = PHP_EOL;

    public function setDelimiters(string $lines = PHP_EOL): self
    {
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

            $records[] = array_map([$this, 'unescapeNl'], str_getcsv($line, ',', '"', '\\'));
        }
        return $records;
    }

    public function pack(array $content): string
    {
        $lines = [];
        foreach ($content as &$record) {
            $record = (array) $record;
            ksort($record);
            $record[] = ''; // separator on end
            $lines[] = $this->str_putcsv(array_map([$this, 'escapeNl'], $record));
        }
        return implode($this->delimitLines, $lines);
    }

    /**
     * @param array<string|int, string|int|float|array<string|int, string|int>> $array
     * @param string $delimiter
     * @param string $enclosure
     * @param string $terminator
     * @return string
     * @link https://www.php.net/manual/en/function.str-getcsv.php#88353
     * @codeCoverageIgnore better try it live
     */
    protected function str_putcsv(array $array, string $delimiter = ',', string $enclosure = '"', string $terminator = PHP_EOL): string
    {
        # First convert associative array to numeric indexed array
        $workArray = array_values($array);
        $returnString = '';                 # Initialize return string
        $arraySize = count($workArray);     # Get size of array

        for ($i=0; $i<$arraySize; $i++) {
            # Nested array, process nest item
            if (is_array($workArray[$i])) {
                $returnString .= $this->str_putcsv($workArray[$i], $delimiter, $enclosure, $terminator);
            } else {
                switch (gettype($workArray[$i])) {
                    # Manually set some strings
                    case "NULL":     $_spFormat = ''; break;
                    case "boolean":  $_spFormat = (true == $workArray[$i]) ? 'true': 'false'; break;
                    # Make sure sprintf has a good datatype to work with
                    case "integer":  $_spFormat = '%i'; break;
                    case "double":   $_spFormat = '%0.2f'; break;
                    case "string":   $_spFormat = '%s'; break;
                    # Unknown or invalid items for a csv - note: the datatype of array is already handled above, assuming the data is nested
                    case "object":
                    case "resource":
                    default:         $_spFormat = '';
                }
                $returnString .= sprintf('%2$s'.$_spFormat.'%2$s', $workArray[$i], $enclosure);
                $returnString .= ($i < ($arraySize-1)) ? $delimiter : $terminator;
            }
        }
        # Done the workload, return the output information
        return $returnString;
    }
}

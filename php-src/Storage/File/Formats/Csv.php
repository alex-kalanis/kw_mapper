<?php

namespace kalanis\kw_mapper\Storage\File\Formats;


use kalanis\kw_mapper\Interfaces\IFileFormat;


/**
 * Class Csv
 * @package kalanis\kw_mapper\Storage\File\Formats
 * Store it in CSV table
 */
class Csv implements IFileFormat
{
    use TNl;

    protected $delimitLines = PHP_EOL;

    public function setDelimiters(string $lines = PHP_EOL): self
    {
        $this->delimitLines = $lines;
        return $this;
    }

    public function unpack(string $content): array
    {
        $lines = str_getcsv($content, $this->delimitLines); //parse the rows
        $records = [];
        foreach ($lines as &$line) {
            if (empty($line)) {
                continue;
            }

            $records[] = array_map([$this, 'strToNl'], str_getcsv($line));
        }
        return $records;
    }

    public function pack(array $records): string
    {
        $lines = [];
        foreach ($records as &$record) {
            ksort($record);
            $record[] = ''; // separator on end
            $lines[] = $this->str_putcsv(array_map([$this, 'nlToStr'], $record));
        }
        return implode($this->delimitLines, $lines);
    }

    /**
     * @param $array
     * @param string $delimiter
     * @param string $enclosure
     * @param string $terminator
     * @return string
     * @link https://www.php.net/manual/en/function.str-getcsv.php#88353
     * @codeCoverageIgnore better try it live
     */
    protected function str_putcsv($array, $delimiter = ',', $enclosure = '"', $terminator = "\n") {
        # First convert associative array to numeric indexed array
        foreach ($array as $key => $value) $workArray[] = $value;

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
                    case "boolean":  $_spFormat = ($workArray[$i] == true) ? 'true': 'false'; break;
                    # Make sure sprintf has a good datatype to work with
                    case "integer":  $_spFormat = '%i'; break;
                    case "double":   $_spFormat = '%0.2f'; break;
                    case "string":   $_spFormat = '%s'; break;
                    # Unknown or invalid items for a csv - note: the datatype of array is already handled above, assuming the data is nested
                    case "object":
                    case "resource":
                    default:         $_spFormat = ''; break;
                }
                $returnString .= sprintf('%2$s'.$_spFormat.'%2$s', $workArray[$i], $enclosure);
                $returnString .= ($i < ($arraySize-1)) ? $delimiter : $terminator;
            }
        }
        # Done the workload, return the output information
        return $returnString;
    }

}

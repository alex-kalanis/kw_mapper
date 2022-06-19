<?php

namespace kalanis\kw_mapper\Storage\File\Formats;


use kalanis\kw_mapper\Interfaces\INl;


/**
 * Trait TNl
 * @package kalanis\kw_mapper\Storage\File\Formats
 */
trait TNl
{
    /** @var string */
    protected $delimitElements = '|';

    /** @var array<string, string> */
    protected static $escapeNlTr = [
        "\r\n" => INl::CRLF_REPLACEMENT,
        "\r" => INl::CR_REPLACEMENT,
        "\n" => INl::LF_REPLACEMENT,
    ];

    public function unescapeNl(string $content): string
    {
        static::$escapeNlTr[$this->delimitElements] = INl::SEP_REPLACEMENT;
        return strtr($content, array_flip(static::$escapeNlTr));
    }

    /**
     * @param mixed $content
     * @return string
     */
    public function escapeNl($content): string
    {
        static::$escapeNlTr[$this->delimitElements] = INl::SEP_REPLACEMENT;
        return strtr(strval($content), static::$escapeNlTr);
    }
}

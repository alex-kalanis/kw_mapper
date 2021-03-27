<?php

namespace kalanis\kw_mapper\Storage\File\Formats;


use kalanis\kw_mapper\Interfaces\INl;


/**
 * Trait TNl
 * @package kalanis\kw_mapper\Storage\File\Formats
 */
trait TNl
{
    public function strToNl(string $content): string
    {
        return strtr($content, INl::NL_REPLACEMENT, "\r\n");
    }

    public function nlToStr($content): string
    {
        $content = strval($content);
        return strtr(
            strtr(
                strtr(
                    strtr($content, "\r\n", INl::NL_REPLACEMENT),
                    "\n\r", INl::NL_REPLACEMENT),
                "\r", INl::NL_REPLACEMENT),
            "\n", INl::NL_REPLACEMENT
        );
    }
}

<?php

namespace kalanis\kw_mapper\Interfaces;


/**
 * Interface INl
 * @package kalanis\kw_mapper\Interfaces
 * What to replace new line
 */
interface INl
{
    public const CR_REPLACEMENT = '---!!::CR::!!---';
    public const LF_REPLACEMENT = '---!!::NL::!!---';
    public const CRLF_REPLACEMENT = '---!!::CRLF::!!---';
    public const SEP_REPLACEMENT = '---!!::SEP::!!---';
}

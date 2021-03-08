<?php

namespace kalanis\kw_mapper\Records;


use kalanis\kw_mapper\Interfaces\IEntryType;


/**
 * Class PageRecord
 * @package kalanis\kw_mapper\Records
 * @property string path
 * @property string content
 */
class PageRecord extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('path', IEntryType::TYPE_STRING, 512);
        $this->addEntry('content', IEntryType::TYPE_STRING, PHP_INT_MAX);
        $this->setMapper('\kalanis\kw_mapper\Mappers\File\PageContent');
    }
}

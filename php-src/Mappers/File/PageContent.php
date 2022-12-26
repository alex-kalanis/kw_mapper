<?php

namespace kalanis\kw_mapper\Mappers\File;


use kalanis\kw_mapper\Records;
use kalanis\kw_mapper\Storage\Shared\FormatFiles\SinglePage;


/**
 * Class PageContent
 * @package kalanis\kw_mapper\Mappers\File
 * Single entry as set in path key from defined source
 */
class PageContent extends AFile
{
    protected function setMap(): void
    {
        $this->setPathKey('path');
        $this->setContentKey('content');
        $this->setFormat(SinglePage::class);
    }

    public function loadMultiple(Records\ARecord $record): array
    {
        $this->load($record);
        return [$record];
    }
}

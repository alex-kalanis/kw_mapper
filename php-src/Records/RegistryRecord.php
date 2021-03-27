<?php

namespace kalanis\kw_mapper\Records;


use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Interfaces\IRegistry;


/**
 * Class RegistryRecord
 * @package kalanis\kw_mapper\Records
 * @property int part
 * @property string path
 * @property string type
 * @property string content
 */
class RegistryRecord extends AStrictRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('part', IEntryType::TYPE_SET, [IRegistry::HKEY_CLASSES_ROOT, IRegistry::HKEY_CURRENT_CONFIG, IRegistry::HKEY_CURRENT_USER, IRegistry::HKEY_LOCAL_MACHINE, IRegistry::HKEY_USERS, ]);
        $this->addEntry('path', IEntryType::TYPE_STRING, 1024);
        $this->addEntry('type', IEntryType::TYPE_SET, [IRegistry::REG_BINARY, IRegistry::REG_DWORD, IRegistry::REG_EXPAND_SZ, IRegistry::REG_MULTI_SZ, IRegistry::REG_NONE, IRegistry::REG_SZ, ]);
        $this->addEntry('content', IEntryType::TYPE_STRING, PHP_INT_MAX);
        $this->setMapper('\kalanis\kw_mapper\Mappers\File\PageContent');
    }
}

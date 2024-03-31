<?php

namespace kalanis\kw_mapper\Interfaces;


/**
 * Interface IRegistry
 * @package kalanis\kw_mapper\Interfaces
 * Registry constants
 * Need to map onto real constants in your system
 */
interface IRegistry
{
    /* Registry main key constants */
    public const HKEY_CLASSES_ROOT = 0;
    public const HKEY_CURRENT_CONFIG = 1;
    public const HKEY_CURRENT_USER = 2;
    public const HKEY_LOCAL_MACHINE = 3;
    public const HKEY_USERS = 4;

    /* Registry access type */
    public const KEY_ALL_ACCESS = 'acc_all';
    public const KEY_WRITE = 'acc_write';
    public const KEY_READ = 'acc_read';

    /* Registry value type */
    public const REG_BINARY = 'binary'; //-> value is a binary string
    public const REG_DWORD = 'dword'; //-> value is stored as a 32-bit long integer
    public const REG_EXPAND_SZ = 'expand_sz'; //-> value is stored as a variable-length string
    public const REG_MULTI_SZ = 'multi_sz'; //-> value is a list of items separated by a delimiter such as a space or comma
    public const REG_NONE = 'none'; //-> value has no particular data type associated with it
    public const REG_SZ = 'sz'; //-> value is stored as a fixed-length string
}

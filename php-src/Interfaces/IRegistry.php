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
    const HKEY_CLASSES_ROOT = 0;
    const HKEY_CURRENT_CONFIG = 1;
    const HKEY_CURRENT_USER = 2;
    const HKEY_LOCAL_MACHINE = 3;
    const HKEY_USERS = 4;

    /* Registry access type */
    const KEY_ALL_ACCESS = 'acc_all';
    const KEY_WRITE = 'acc_write';
    const KEY_READ = 'acc_read';

    /* Registry value type */
    const REG_BINARY = 'binary'; //-> value is a binary string
    const REG_DWORD = 'dword'; //-> value is stored as a 32-bit long integer
    const REG_EXPAND_SZ = 'expand_sz'; //-> value is stored as a variable-length string
    const REG_MULTI_SZ = 'multi_sz'; //-> value is a list of items separated by a delimiter such as a space or comma
    const REG_NONE = 'none'; //-> value has no particular data type associated with it
    const REG_SZ = 'sz'; //-> value is stored as a fixed-length string
}

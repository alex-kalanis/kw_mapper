<?php

namespace kalanis\kw_mapper\Interfaces;


/**
 * Interface IDriverSources
 * @package kalanis\kw_mapper\Interfaces
 * Types of sources which can be targeted
 */
interface IDriverSources
{
    const TYPE_PDO_MYSQL = 'mysql';
    const TYPE_PDO_MSSQL = 'mssql';
    const TYPE_PDO_ORACLE = 'oracle';
    const TYPE_PDO_POSTGRES = 'postgres';
    const TYPE_PDO_SQLITE = 'sqlite';
    const TYPE_RAW_MYSQLI = 'mysqlnd';
    const TYPE_RAW_MONGO = 'mongodb';
    const TYPE_RAW_LDAP = 'ldap';
    const TYPE_RAW_WINREG = 'win-registry';
    const TYPE_RAW_DBA = 'dba';
    const TYPE_ODBC = 'odbc';

    const ACTION_INSERT = 'add';
    const ACTION_UPDATE = 'upd';
    const ACTION_DELETE = 'del';
}

<?php

namespace kalanis\kw_mapper\Interfaces;


/**
 * Interface IDriverSources
 * @package kalanis\kw_mapper\Interfaces
 * Types of sources which can be targeted
 */
interface IDriverSources
{
    public const TYPE_PDO_MYSQL = 'mysql';
    public const TYPE_PDO_MSSQL = 'mssql';
    public const TYPE_PDO_MSSQL_T = 'mssqlt';
    public const TYPE_PDO_ORACLE = 'oracle';
    public const TYPE_PDO_POSTGRES = 'postgres';
    public const TYPE_PDO_SQLITE = 'sqlite';
    public const TYPE_RAW_MYSQLI = 'mysqlnd';
    public const TYPE_RAW_ORACLE = 'oracle8';
    public const TYPE_RAW_MONGO = 'mongodb';
    public const TYPE_RAW_LDAP = 'ldap';
    public const TYPE_RAW_WINREG = 'win-registry';
    public const TYPE_RAW_WINREG2 = 'win-registry-net';
    public const TYPE_RAW_DBA = 'dba';
    public const TYPE_ODBC = 'odbc';

    public const ACTION_INSERT = 'add';
    public const ACTION_UPDATE = 'upd';
    public const ACTION_DELETE = 'del';
}

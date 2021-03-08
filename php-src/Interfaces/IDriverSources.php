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
}

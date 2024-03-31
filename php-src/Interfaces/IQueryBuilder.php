<?php

namespace kalanis\kw_mapper\Interfaces;


/**
 * Interface IQueryBuilder
 * @package kalanis\kw_mapper\Interfaces
 * Types of available operations in query builder
 * They are checked on set
 */
interface IQueryBuilder
{
    public const RELATION_AND = 'AND';
    public const RELATION_OR = 'OR';

    public const ORDER_ASC = 'ASC';
    public const ORDER_DESC = 'DESC';

    public const OPERATION_NULL = 'nul';    // is null
    public const OPERATION_NNULL = 'nnul';  // is not null
    public const OPERATION_EQ = 'eq';       // =
    public const OPERATION_NEQ = 'neq';     // !=
    public const OPERATION_GT = 'gt';       // >
    public const OPERATION_GTE = 'gte';     // >=
    public const OPERATION_LT = 'lt';       // <
    public const OPERATION_LTE = 'lte';     // <=
    public const OPERATION_LIKE = 'like';   // like...
    public const OPERATION_NLIKE = 'nlike'; // not like
    public const OPERATION_REXP = 'rexp';   // regex
    public const OPERATION_IN = 'in';       // in ()
    public const OPERATION_NIN = 'nin';     // not in ()

    public const AGGREGATE_AVG = 'AVG';
    public const AGGREGATE_COUNT = 'COUNT';
    public const AGGREGATE_MIN = 'MIN';
    public const AGGREGATE_MAX = 'MAX';
    public const AGGREGATE_SUM = 'SUM';

    public const JOIN_BASIC = '';
    public const JOIN_LEFT = 'LEFT';
    public const JOIN_LEFT_OUTER = 'LEFT OUTER';
    public const JOIN_RIGHT = 'RIGHT';
    public const JOIN_INNER = 'INNER';
    public const JOIN_OUTER = 'OUTER';
    public const JOIN_CROSS = 'CROSS';
    public const JOIN_FULL = 'FULL';
}

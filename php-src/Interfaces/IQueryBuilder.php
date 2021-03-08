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
    const RELATION_AND = 'AND';
    const RELATION_OR = 'OR';

    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    const OPERATION_NULL = 'nul';    // is null
    const OPERATION_NNULL = 'nnul';  // is not null
    const OPERATION_EQ = 'eq';       // =
    const OPERATION_NEQ = 'neq';     // !=
    const OPERATION_GT = 'gt';       // >
    const OPERATION_GTE = 'gte';     // >=
    const OPERATION_LT = 'lt';       // <
    const OPERATION_LTE = 'lte';     // <=
    const OPERATION_LIKE = 'like';   // like...
    const OPERATION_NLIKE = 'nlike'; // not like
    const OPERATION_REXP = 'rexp';   // regex
    const OPERATION_IN = 'in';       // in ()
    const OPERATION_NIN = 'nin';     // not in ()

    const AGGREGATE_AVG = 'AVG';
    const AGGREGATE_COUNT = 'COUNT';
    const AGGREGATE_MIN = 'MIN';
    const AGGREGATE_MAX = 'MAX';
    const AGGREGATE_SUM = 'SUM';

    const JOIN_BASIC = '';
    const JOIN_LEFT = 'LEFT';
    const JOIN_LEFT_OUTER = 'LEFT OUTER';
    const JOIN_RIGHT = 'RIGHT';
    const JOIN_INNER = 'INNER';
    const JOIN_OUTER = 'OUTER';
    const JOIN_CROSS = 'CROSS';
    const JOIN_FULL = 'FULL';
}

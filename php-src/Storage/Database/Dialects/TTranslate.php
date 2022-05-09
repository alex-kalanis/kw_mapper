<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;


/**
 * Trait TTranslate
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * Translate actions in query builder into db operands
 */
trait TTranslate
{
    /**
     * @param string $operation
     * @return string
     * @throws MapperException
     */
    public function translateOperation(string $operation): string
    {
        switch ($operation) {
            case IQueryBuilder::OPERATION_NULL:
                return 'IS NULL';
            case IQueryBuilder::OPERATION_NNULL:
                return 'IS NOT NULL';
            case IQueryBuilder::OPERATION_EQ:
                return '=';
            case IQueryBuilder::OPERATION_NEQ:
                return '!=';
            case IQueryBuilder::OPERATION_GT:
                return '>';
            case IQueryBuilder::OPERATION_GTE:
                return '>=';
            case IQueryBuilder::OPERATION_LT:
                return '<';
            case IQueryBuilder::OPERATION_LTE:
                return '<=';
            case IQueryBuilder::OPERATION_LIKE:
                return 'LIKE';
            case IQueryBuilder::OPERATION_NLIKE:
                return 'NOT LIKE';
            case IQueryBuilder::OPERATION_REXP:
                return 'REGEX';
            case IQueryBuilder::OPERATION_IN:
                return 'IN';
            case IQueryBuilder::OPERATION_NIN:
                return 'NOT IN';
            default:
                throw new MapperException(sprintf('Unknown operation *%s*', $operation));
        }
    }

    /**
     * @param string $operation
     * @param string|string[] $columnKey
     * @return string
     * @throws MapperException
     */
    public function translateKey(string $operation, $columnKey): string
    {
        switch ($operation) {
            case IQueryBuilder::OPERATION_NULL:
            case IQueryBuilder::OPERATION_NNULL:
                return '';
            case IQueryBuilder::OPERATION_EQ:
            case IQueryBuilder::OPERATION_NEQ:
            case IQueryBuilder::OPERATION_GT:
            case IQueryBuilder::OPERATION_GTE:
            case IQueryBuilder::OPERATION_LT:
            case IQueryBuilder::OPERATION_LTE:
            case IQueryBuilder::OPERATION_LIKE:
            case IQueryBuilder::OPERATION_NLIKE:
            case IQueryBuilder::OPERATION_REXP:
                return strval($columnKey);
            case IQueryBuilder::OPERATION_IN:
            case IQueryBuilder::OPERATION_NIN:
                return sprintf('(%s)', implode(',', $this->notEmptyArray($columnKey)));
            default:
                throw new MapperException(sprintf('Unknown operation *%s*', $operation));
        }
    }

    protected function notEmptyArray($array): array
    {
        if (empty($array)) {
            return [0];
        }
        return (array)$array;
    }
}

<?php

namespace kalanis\kw_mapper\Storage\Database;


use kalanis\kw_mapper\MapperException;


/**
 * trait TBindNames
 * @package kalanis\kw_mapper\Storage\Database
 */
trait TBindNames
{
    /**
     * @param string $query
     * @param array $params
     * @return array
     * @throws MapperException
     */
    public function bindFromNamedToQuestions(string $query, array $params): array
    {
        $binds = [];
        $types = [];
        if (empty($params)) {
            return [$query, $binds, $types];
        }
        while (false !== ($pos = strpos($query, ':'))) {
            $nextSpace = strpos($query, ' ', $pos);
            $key = ($nextSpace) ? substr($query, $pos, $nextSpace - $pos) : substr($query, $pos);
            if (!isset($params[$key])) {
                throw new MapperException(sprintf('Unknown bind for key *%s*', $key));
            }
            $binds[] = $params[$key];
            $types[] = $this->getTypeOf($params[$key]);
            $query = substr($query, 0, $pos) . '?' . ( $nextSpace ? substr($query, $nextSpace) : '' );
        }
        return [$query, $binds, $types];
    }

    protected function getTypeOf($var): string
    {
        if (is_bool($var)) {
            return 'i';
        } elseif (is_int($var)) {
            return 'i';
        } elseif (is_float($var)) {
            return 'd';
        } else {
            return 's';
        }
    }
}

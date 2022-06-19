<?php

namespace kalanis\kw_mapper\Storage\Database\Dialects;


use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\TFill;
use kalanis\kw_mapper\Storage\Shared\QueryBuilder;


/**
 * Class LdapQueries
 * @package kalanis\kw_mapper\Storage\Database\Dialects
 * LDAP queries
 * @link https://ldap.com/ldap-dns-and-rdns/
 * @link https://docs.microsoft.com/cs-cz/windows/win32/adsi/search-filter-syntax?redirectedfrom=MSDN
 * @link https://www.php.net/manual/en/function.ldap-search.php#28593
 * @link https://docs.ldap.com/specs/rfc4514.txt
 */
class LdapQueries
{
    use TFill;

    /** @var array<string, string> */
    protected $sanitizer = [
        ' ' => '\20',
        '#' => '\23',
        '"' => '\22',
        '+' => '\2b',
        ',' => '\2c',
        ';' => '\3b',
        '<' => '\3c',
        '=' => '\3d',
        '>' => '\3e',
        '\\' => '\5c',
    ];

    /**
     * @param string $domain
     * @return string
     *
     * The domain is simple http link ->
     * http://username:password@hostname.tld:9090/path/to/somewhere?
     * where hostname.tld is parsed as domain component and /path/to/somewhere as organization units
     */
    public function domainDn(string $domain): string
    {
        $parsed = parse_url($domain);

        if (false === $parsed || empty($parsed['host']) || empty($parsed['path'])) {
            return '';
        }

        $parts = explode('.', $parsed['host']);
        $tld = array_slice($parts, -1, 1);
        $domain = array_slice($parts, -2, 1);
        $trailed = array_filter(explode('/', $parsed['path']));

        $locators = [];
        $subs = count($trailed);
        if (2 < $subs) {
            $locators[] = 'uid=' . $this->sanitizeDn(strval(end($trailed)));
            $locators[] = 'ou=' . $this->sanitizeDn(strval(prev($trailed)));
            $locators[] = 'cn=' . $this->sanitizeDn(strval(prev($trailed)));
        } elseif (1 < $subs) {
            $locators[] = 'ou=' . $this->sanitizeDn(strval(end($trailed)));
            $locators[] = 'cn=' . $this->sanitizeDn(strval(prev($trailed)));
        } elseif ($subs) {
            $locators[] = 'cn=' . $this->sanitizeDn(strval(end($trailed)));
        }
        $locators[] = 'dc=' . $this->sanitizeDn(strval(reset($domain)));
        $locators[] = 'dc=' . $this->sanitizeDn(strval(reset($tld)));
        return implode(',', $locators);
    }

    public function userDn(string $domain, string $username): string
    {
        $parsed = parse_url($domain);

        if (false === $parsed || empty($parsed['host']) || empty($parsed['path'])) {
            return '';
        }

        $parts = explode('.', $parsed['host']);
        $tld = array_slice($parts, -1, 1);
        $domain = array_slice($parts, -2, 1);
        $trailed = array_filter(explode('/', $parsed['path']));

        $locators = [];
        $locators[] = 'uid=' . $this->sanitizeDn($username);
        $subs = count($trailed);
        if (1 < $subs) {
            $locators[] = 'ou=' . $this->sanitizeDn(strval(end($trailed)));
            $locators[] = 'cn=' . $this->sanitizeDn(strval(prev($trailed)));
        } elseif ($subs) {
            $locators[] = 'cn=' . $this->sanitizeDn(strval(end($trailed)));
        }
        $locators[] = 'dc=' . $this->sanitizeDn(strval(reset($domain)));
        $locators[] = 'dc=' . $this->sanitizeDn(strval(reset($tld)));
        return implode(',', $locators);
    }

    protected function sanitizeDn(string $dn): string
    {
        return strtr($dn, $this->sanitizer);
    }

    /**
     * @param QueryBuilder $builder
     * @return array<string|int, int|string|float|null>
     */
    public function changed(QueryBuilder $builder): array
    {
        $props = [];
        $params = $builder->getParams();
        foreach ($builder->getProperties() as $property) {
            $props[$property->getColumnName()] = $params[$property->getColumnKey()];
        }
        return $props;
    }

    /**
     * @param QueryBuilder $builder
     * @throws MapperException
     * @return string
     */
    public function filter(QueryBuilder $builder): string
    {
        $cond = [];
        foreach ($builder->getConditions() as $condition) {
            $cond[] = $this->addCompare($condition, $builder->getParams());
        }
        return sprintf('(%s%s)', $this->howMergeRules($builder), implode('', $cond));
    }

    protected function howMergeRules(QueryBuilder $builder): string
    {
        return (IQueryBuilder::RELATION_AND == $builder->getRelation()) ? '&' : '|';
    }

    /**
     * @param QueryBuilder\Condition $condition
     * @param array<string, int|string|float> $params
     * @throws MapperException
     * @return string
     */
    protected function addCompare(QueryBuilder\Condition $condition, array $params): string
    {
        $columnName = strval($condition->getColumnName());
        switch ($condition->getOperation()) {
            case IQueryBuilder::OPERATION_NULL:
                return sprintf('(%s=*)', $columnName);
            case IQueryBuilder::OPERATION_NNULL:
                return sprintf('(!(%s=*))', $columnName);
            case IQueryBuilder::OPERATION_EQ:
                return sprintf('(%s=%s)', $columnName, $params[strval($condition->getColumnKey())]);
            case IQueryBuilder::OPERATION_NEQ:
                return sprintf('(!(%s=%s))', $columnName, $params[strval($condition->getColumnKey())]);
            case IQueryBuilder::OPERATION_GT:
                return sprintf('(%s>%s)', $columnName, $params[strval($condition->getColumnKey())]);
            case IQueryBuilder::OPERATION_GTE:
                return sprintf('(%s>=%s)', $columnName, $params[strval($condition->getColumnKey())]);
            case IQueryBuilder::OPERATION_LT:
                return sprintf('(%s<%s)', $columnName, $params[strval($condition->getColumnKey())]);
            case IQueryBuilder::OPERATION_LTE:
                return sprintf('(%s<=%s)', $columnName, $params[strval($condition->getColumnKey())]);
            case IQueryBuilder::OPERATION_LIKE:
                return sprintf('(%s=%s)', $columnName, $this->changePercents(strval($params[strval($condition->getColumnKey())])));
            case IQueryBuilder::OPERATION_NLIKE:
                return sprintf('(!(%s=%s))', $columnName, $this->changePercents(strval($params[strval($condition->getColumnKey())])));
            case IQueryBuilder::OPERATION_IN:
                return sprintf('(|%s)', $this->changeIn($columnName, $condition->getColumnKey(), $params));
            case IQueryBuilder::OPERATION_NIN:
                return sprintf('(!(|%s))', $this->changeIn($columnName, $condition->getColumnKey(), $params));
            case IQueryBuilder::OPERATION_REXP:
            default:
                throw new MapperException(sprintf('Unknown operation *%s*!', $condition->getOperation()));
        }
    }

    protected function changePercents(string $in): string
    {
        return strtr($in, ['%' => '*']);
    }

    /**
     * @param string $columnName
     * @param string|string[] $keys
     * @param array<string, int|string|float> $params
     * @return string
     */
    protected function changeIn(string $columnName, $keys, array $params): string
    {
        if (!is_array($keys)) {
            $keys = (array) $keys;
        }
        if (empty($keys)) {
            return sprintf('(%s=0)', $columnName);
        }
        $vars = [];
        foreach ($keys as $key) {
            $val = isset($params[$key]) ? $params[$key] : '0';
            $vars[] = sprintf('(%s=%s)', $columnName, $val);
        }
        return implode('', $vars);
    }
}

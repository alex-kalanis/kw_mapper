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

        $parts = explode('.', $parsed['host']);
        $tld = array_slice($parts, -1, 1);
        $domain = array_slice($parts, -2, 1);
        $trailed = array_filter(explode('/', $parsed['path']));

        $locators = [];
        $subs = count($trailed);
        if (2 < $subs) {
            $locators[] = 'uid=' . $this->sanitizeDn(end($trailed));
            $locators[] = 'ou=' . $this->sanitizeDn(prev($trailed));
            $locators[] = 'cn=' . $this->sanitizeDn(prev($trailed));
        } elseif (1 < $subs) {
            $locators[] = 'ou=' . $this->sanitizeDn(end($trailed));
            $locators[] = 'cn=' . $this->sanitizeDn(prev($trailed));
        } elseif ($subs) {
            $locators[] = 'cn=' . $this->sanitizeDn(end($trailed));
        }
        $locators[] = 'dc=' . $this->sanitizeDn(reset($domain));
        $locators[] = 'dc=' . $this->sanitizeDn(reset($tld));
        return implode(',', $locators);
    }

    public function userDn(string $domain, $username): string
    {
        $parsed = parse_url($domain);

        $parts = explode('.', $parsed['host']);
        $tld = array_slice($parts, -1, 1);
        $domain = array_slice($parts, -2, 1);
        $trailed = array_filter(explode('/', $parsed['path']));

        $locators = [];
        $locators[] = 'uid=' . $this->sanitizeDn($username);
        $subs = count($trailed);
        if (1 < $subs) {
            $locators[] = 'ou=' . $this->sanitizeDn(end($trailed));
            $locators[] = 'cn=' . $this->sanitizeDn(prev($trailed));
        } elseif ($subs) {
            $locators[] = 'cn=' . $this->sanitizeDn(end($trailed));
        }
        $locators[] = 'dc=' . $this->sanitizeDn(reset($domain));
        $locators[] = 'dc=' . $this->sanitizeDn(reset($tld));
        return implode(',', $locators);
    }

    protected function sanitizeDn(string $dn): string
    {
        return strtr($dn, $this->sanitizer);
    }

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
     * @return string
     * @throws MapperException
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
     * @param array $params
     * @return string
     * @throws MapperException
     */
    protected function addCompare(QueryBuilder\Condition $condition, array $params): string
    {
        switch ($condition->getOperation()) {
            case IQueryBuilder::OPERATION_NULL:
                return sprintf('(%s=*)', $condition->getColumnName());
            case IQueryBuilder::OPERATION_NNULL:
                return sprintf('(!(%s=*))', $condition->getColumnName());
            case IQueryBuilder::OPERATION_EQ:
                return sprintf('(%s=%s)', $condition->getColumnName(), $params[$condition->getColumnKey()]);
            case IQueryBuilder::OPERATION_NEQ:
                return sprintf('(!(%s=%s))', $condition->getColumnName(), $params[$condition->getColumnKey()]);
            case IQueryBuilder::OPERATION_GT:
                return sprintf('(%s>%s)', $condition->getColumnName(), $params[$condition->getColumnKey()]);
            case IQueryBuilder::OPERATION_GTE:
                return sprintf('(%s>=%s)', $condition->getColumnName(), $params[$condition->getColumnKey()]);
            case IQueryBuilder::OPERATION_LT:
                return sprintf('(%s<%s)', $condition->getColumnName(), $params[$condition->getColumnKey()]);
            case IQueryBuilder::OPERATION_LTE:
                return sprintf('(%s<=%s)', $condition->getColumnName(), $params[$condition->getColumnKey()]);
            case IQueryBuilder::OPERATION_LIKE:
                return sprintf('(%s=%s)', $condition->getColumnName(), $this->changePercents($params[$condition->getColumnKey()]));
            case IQueryBuilder::OPERATION_NLIKE:
                return sprintf('(!(%s=%s))', $condition->getColumnName(), $this->changePercents($params[$condition->getColumnKey()]));
            case IQueryBuilder::OPERATION_IN:
                return sprintf('(|%s)', $this->changeIn($condition->getColumnName(), $params[$condition->getColumnKey()]));
            case IQueryBuilder::OPERATION_NIN:
                return sprintf('(!(|%s))', $this->changeIn($condition->getColumnName(), $params[$condition->getColumnKey()]));
            case IQueryBuilder::OPERATION_REXP:
            default:
                throw new MapperException(sprintf('Unknown operation *%s*!', $condition->getOperation()));
        }
    }

    protected function changePercents(string $in): string
    {
        return strtr($in, ['%' => '*']);
    }

    protected function changeIn(string $columnName, array $params): string
    {
        if (empty($params)) {
            return sprintf('(%s=0)', $columnName);
        }
        $vars = [];
        foreach ($params as $param) {
            $vars[] = sprintf('(%s=%s)', $columnName, $param);
        }
        return implode('', $vars);
    }
}

<?php

namespace StorageTests\Database\Dialects;


use Builder;
use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IQueryBuilder;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Dialects\LdapQueries;


class LdapTest extends CommonTestClass
{
    public function testDomain()
    {
        $sql = new LdapQueries();
        $this->assertEquals('uid=name,ou=organization,cn=on,dc=server,dc=tld', $sql->domainDn('ldaps://someone:logged@server.tld:136/on/organization/name/'));
        $this->assertEquals('ou=organization,cn=on,dc=server,dc=tld', $sql->domainDn('ldaps://someone:logged@server.tld:136/on/organization/'));
        $this->assertEquals('cn=on,dc=server,dc=tld', $sql->domainDn('ldaps://someone:logged@server.tld:136/on/'));
    }

    public function testUser()
    {
        $sql = new LdapQueries();
        $this->assertEquals('uid=\23myself,ou=name,cn=organization,dc=server,dc=tld', $sql->userDn('ldaps://someone:logged@server.tld:136/on/organization/name/', '#myself'));
        $this->assertEquals('uid=\23myself,ou=organization,cn=on,dc=server,dc=tld', $sql->userDn('ldaps://someone:logged@server.tld:136/on/organization/', '#myself'));
        $this->assertEquals('uid=\23myself,cn=on,dc=server,dc=tld', $sql->userDn('ldaps://someone:logged@server.tld:136/on/', '#myself'));
    }

    /**
     * @throws MapperException
     */
    public function testUpdate()
    {
        $query = new Builder();
        $query->setBaseTable('foo');
        $query->addProperty('foo', 'bar', 'baz');
        $query->addProperty('foo', 'htf', 'yjd');
        $query->addProperty('foo', 'vrs', 'abh');
        $sql = new LdapQueries();
        $this->assertEquals([ 'bar' => 'baz', 'htf' => 'yjd', 'vrs' => 'abh', ], $sql->changed($query));
        $query->resetCounter();
    }

    /**
     * @throws MapperException
     */
    public function testFilter()
    {
        $query = new Builder();
        $query->setBaseTable('foo');
        $query->addCondition('foo', 'dbt', IQueryBuilder::OPERATION_EQ, 'ggf');
        $query->addCondition('foo', 'dfd', IQueryBuilder::OPERATION_NEQ, 'yxn');
        $query->addCondition('foo', 'dhd', IQueryBuilder::OPERATION_NULL, 'ydf');
        $query->addCondition('foo', 'hjx', IQueryBuilder::OPERATION_NNULL, 'nhf');
        $query->addCondition('foo', 'hdz', IQueryBuilder::OPERATION_GT, 'bfd');
        $query->addCondition('foo', 'gnd', IQueryBuilder::OPERATION_GTE, 'btj');
        $query->addCondition('foo', 'xhj', IQueryBuilder::OPERATION_LT, 'xdf');
        $query->addCondition('foo', 'gdg', IQueryBuilder::OPERATION_LTE, 'djs');
        $query->addCondition('foo', 'xgj', IQueryBuilder::OPERATION_LIKE, 'yxh');
        $query->addCondition('foo', 'bgf', IQueryBuilder::OPERATION_NLIKE, 'yhy');
        $query->addCondition('foo', 'ydf', IQueryBuilder::OPERATION_IN, ['bzy', 'gjf', ]);
        $query->addCondition('foo', 'ybf', IQueryBuilder::OPERATION_NIN, []);
        $sql = new LdapQueries();
        $this->assertEquals('(&(dbt=ggf)(!(dfd=yxn))(dhd=*)(!(hjx=*))(hdz>bfd)(gnd>=btj)(xhj<xdf)(gdg<=djs)(xgj=yxh)(!(bgf=yhy))(|(ydf=bzy)(ydf=gjf))(!(|(ybf=0))))', $sql->filter($query));

        $this->expectException(MapperException::class);
        $query->addCondition('foo', 'dfd', IQueryBuilder::OPERATION_REXP, 'yxn');
        $query->resetCounter();
        $sql->filter($query);
    }
}

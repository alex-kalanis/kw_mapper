<?php

namespace StorageTests\Database\Dialects;


use Builder;
use CommonTestClass;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage\Database\Dialects\EmptyDialect;


class EmptyTest extends CommonTestClass
{
    /**
     * @throws MapperException
     */
    public function testAll()
    {
        $qb = new Builder();
        $sql = new EmptyDialect();
        $this->assertEmpty($sql->describe($qb));
        $this->assertEmpty($sql->insert($qb));
        $this->assertEmpty($sql->update($qb));
        $this->assertEmpty($sql->select($qb));
        $this->assertEmpty($sql->delete($qb));
        $this->assertEmpty($sql->availableJoins());
    }
}

<?php

namespace DatabaseTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Storage;


class SqLiteTest extends CommonTestClass
{
    public function testContentOk(): void
    {
        try {
            $conf = new Storage\Database\Config();
            $conf->setTarget(IDriverSources::TYPE_PDO_SQLITE, 'test_sqlite', ':memory:', 0, null, null, '');
            $pdo = Storage\Database\Factory::getInstance()->getDatabase($conf);
            /** @var Storage\Database\PDO\SQLite $pdo*/

            $pdo->exec('CREATE TABLE "types" ("id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, "float" DOUBLE PRECISION)', []);
            $pdo->exec('insert into "types" ("float") values (:val)', [':val' => 10]);
            $pdo->exec('update "types" set "float" = :flt', [':flt' => 8.202343767574732]);
            $stored = $pdo->query('select "float" from "types" limit 1', []);
            var_dump($stored);
            var_dump(bin2hex(pack('e', $stored)));

            $pdo->exec('update "types" set "float" = :flt', [':flt' => '8.202343767574732']);
            $stored = $pdo->query('select "float" from "types" limit 1', []);
            var_dump($stored);
            var_dump(bin2hex(pack('e', $stored)));

        } catch (MapperException $ex) {
            $this->assertTrue(false, $ex->getMessage());
        }

        $this->assertTrue(true);
    }
}

<?php

use kalanis\kw_mapper\Interfaces\IType;
use kalanis\kw_mapper\Records;
use kalanis\kw_mapper\Mappers;


class UserRecord extends Records\ARecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IType::TYPE_INTEGER, 16);
        $this->addEntry('name', IType::TYPE_STRING, 128);
        $this->addEntry('password', IType::TYPE_STRING, 128);
        $this->addEntry('enabled', IType::TYPE_BOOLEAN);
        $this->setMapper('\UserDBMapper');
    }
}


class UserDBMapper extends Mappers\Database\MySQL
{
    protected function setMap(): void
    {
        $this->setSource('europe');
        $this->setTable('user');
        $this->setRelation('id', 'u_id');
        $this->setRelation('name', 'u_name');
        $this->setRelation('pass', 'u_pass');
        $this->setRelation('lastLogin', 'u_last_login');
        $this->setPrimaryKey('id');
    }
}


class UserFileMapper extends Mappers\File
{
    protected function setMap(): void
    {
        $this->setFile('users.txt');
        $this->setMode('|', PHP_EOL);
        $this->setPosition('id', 0);
        $this->setPosition('name', 1);
        $this->setPosition('pass', 2);
        $this->setPosition('lastLogin', 3);
        $this->setPrimaryKey('id');
    }
}


/**
 * Class EntryRecord
 * @property int id
 * @property string title
 * @property string content
 * @property kalanis\kw_mapper\Adapters\MappedStdClass details
 * @property int user
 * @property int[] users
 */
class EntryRecord extends Records\ARecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IType::TYPE_INTEGER, 16);
        $this->addEntry('title', IType::TYPE_STRING, 128);
        $this->addEntry('content', IType::TYPE_STRING, 4096);
        $this->addEntry('details', IType::TYPE_OBJECT, '\kalanis\kw_mapper\Adapters\MappedStdClass');
        $this->addEntry('user', IType::TYPE_INTEGER, 32);
        $this->addEntry('users', IType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper('\EntryDBMapper');
    }
}


class EntryDBMapper extends Mappers\Database\MySQL
{
    protected function setMap(): void
    {
        $this->setSource('asia'); // access to another db source
        $this->setTable('entry');
        $this->setRelation('id', 'e_id');
        $this->setRelation('title', 'e_title');
        $this->setRelation('content', 'e_content');
        $this->setRelation('details', 'e_details');
        $this->setRelation('user', 'u_id'); // FK n:1 - many entries, one user
        $this->setPrimaryKey('id'); // local key
        $this->setForeignKey('users', 'user', 'id'); // entry var, local key, remote key
    }

    /**
     * @param Records\ARecord|EntryRecord $entry
     * @return bool
     */
    public function beforeSave(Records\ARecord $entry): bool
    {
        $entry->details = json_encode($entry->details);
        return true;
    }

    /**
     * @param Records\ARecord|EntryRecord $entry
     * @return bool
     */
    public function afterLoad(Records\ARecord $entry): bool
    {
        $entry->details = json_decode($entry->details);
        return true;
    }
}


$search = new Search(new EntryRecord());
$search->child('user');
$search->like('user.name', 'foo');

$pager = new Pager(); // already done - kw_pager
$pager->maxResults($search->count());
$pager->setPage(2);
$search->setOffset($pager->getOffset());
$search->setLimit($pager->getLimit());
$search->setPager($pager); // in extension, base do not need that

$results = $search->getResults();


/// tri nastaveni - soubor, tabulka a soubor s tabulkou
/// prvni ma pk jmeno souboru
/// druhy ma pk definovane mimo
/// treti ma pk jmeno ale pod contentem je dalsi objekt - pole entries
///
/// Idea: Mam admin ucty, ktere maji lokalni nastaveni a overuji se pres ldap
/// Lokalne je profilova fotka, ktera ale ma cestu definovanou v ldapu
/// Pri schvaleni (nalezeni entry) se natahnou data z ldapu a pak se z remote stahne ta fotka jako dalsi entry vazana na ldap
///
/// nahore (na abstrakci) bude jen setMap() a oddelovac typu v aplikaci (zatim tecka, dokazu si tam ale predstavit treba #)
/// pak tam budou veci jako beforeSave() a afterLoad() - to, co se ma s objektem pachat okolo (bezva pro audity)

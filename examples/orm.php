<?php

use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records;
use kalanis\kw_mapper\Search;
use kalanis\kw_mapper\Storage;


# somewhere in bootstrap
Storage\Database\ConfigStorage::getInstance()->addConfig(
    Storage\Database\Config::init()->setTarget(
        IDriverSources::TYPE_PDO_SQLITE, 'europe', '127.0.0.1', 8080, 'testing', 'testing', 'users'
    ));
Storage\Database\ConfigStorage::getInstance()->addConfig(
    Storage\Database\Config::init()->setTarget(
        IDriverSources::TYPE_PDO_SQLITE, 'asia', '::1', 7293, 'testing', 'testing', 'external'
    ));
Storage\Database\ConfigStorage::getInstance()->addConfig(
    Storage\Database\Config::init()->setTarget(
        IDriverSources::TYPE_RAW_LDAP, 'auth', '::1', 9215, 'testing', 'testing', 'tree'
    ));


# then define records and mappers

/**
 * Class UserRecord
 * @property int id
 * @property string name
 * @property string password
 * @property bool enabled
 */
class UserRecord extends Records\AStrictRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('name', IEntryType::TYPE_STRING, 128);
        $this->addEntry('password', IEntryType::TYPE_STRING, 128);
        $this->addEntry('enabled', IEntryType::TYPE_BOOLEAN);
        $this->setMapper('\UserDBMapper');
    }
}


class UserDBMapper extends Mappers\Database\ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('europe');
        $this->setTable('user');
        $this->setRelation('id', 'u_id');
        $this->setRelation('name', 'u_name');
        $this->setRelation('password', 'u_pass');
        $this->setRelation('enabled', 'u_enabled');
        $this->addPrimaryKey('id');
    }
}


class UserFileMapper extends Mappers\File\ATable
{
    protected function setMap(): void
    {
        $this->setFile('users.txt');
        $this->setFormat('\kalanis\kw_mapper\Storage\File\Formats\SeparatedElements');
        $this->setRelation('id', 0);
        $this->setRelation('name', 1);
        $this->setRelation('password', 2);
        $this->setRelation('enabled', 3);
        $this->addPrimaryKey('id');
    }
}


/**
 * Class EntryRecord
 * @property int id
 * @property string title
 * @property string content
 * @property kalanis\kw_mapper\Adapters\MappedStdClass details
 * @property int user
 * @property UserRecord[] users
 */
class EntryRecord extends Records\AStrictRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 65536);
        $this->addEntry('title', IEntryType::TYPE_STRING, 128);
        $this->addEntry('content', IEntryType::TYPE_STRING, 65536);
        $this->addEntry('details', IEntryType::TYPE_OBJECT, '\kalanis\kw_mapper\Adapters\MappedStdClass');
        $this->addEntry('user', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('users', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper('\EntryDBMapper');
    }
}


class EntryDBMapper extends Mappers\Database\ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('asia'); // access to another db source
        $this->setTable('entry');
        $this->setRelation('id', 'e_id');
        $this->setRelation('title', 'e_title');
        $this->setRelation('content', 'e_content');
        $this->setRelation('details', 'e_details');
        $this->setRelation('user', 'u_id');
        $this->addPrimaryKey('id');
        $this->addForeignKey('users', '\UserRecord', 'user', 'id');
    }

    /**
     * @param Records\ARecord|EntryRecord $entry
     * @return bool
     */
    protected function beforeSave(Records\ARecord $entry): bool
    {
        $entry->details = json_encode($entry->details);
        return true;
    }

    /**
     * @param Records\ARecord|EntryRecord $entry
     * @return bool
     */
    protected function afterLoad(Records\ARecord $entry): bool
    {
        $entry->details = json_decode($entry->details);
        return true;
    }
}


try {
    // simple processing with record
    $record = new EntryRecord();
    $record->title = 'qwertzui';
    $record->content = 'asdfghjk';
    $record->user = 55; // fk - user id number
    $record->save();
    $record->load();
    // now can be used

    // want multiple objects
    $record = new EntryRecord();
    $record->title = 'mnbvcxy';
    $records = $record->loadMultiple();
    var_dump($records);

} catch (MapperException $ex) {
    // nothing here
}



try {
    $search = new Search\Search(new EntryRecord());
    $search->child('users');
    $search->exact('users.name', 'foo');
    //$search->like('users|name', 'foo%');
    //$search->like('users|rights|allow', 'bar%');

    $pager = new Pager(); // already done - kw_pager
    $pager->maxResults($search->getCount());
    $pager->setPage(2);
    $search->offset($pager->getOffset());
    $search->limit($pager->getLimit());
    $search->setPager($pager); // in extension, base do not need that

    $results = $search->getResults();
} catch (MapperException $ex) {
    // nothing here
}


/// @todo:
/// tri nastaveni - soubor, tabulka a soubor s tabulkou
/// prvni ma pk jmeno souboru
/// druhy ma pk definovane mimo
/// treti ma pk jmeno ale pod contentem je dalsi objekt - pole entries
///
/// Idea: Mam admin ucty, ktere maji lokalni nastaveni a overuji se pres ldap
/// Lokalne je profilova fotka, ktera ale ma cestu definovanou v ldapu
/// Pri schvaleni (nalezeni entry) se natahnou data z ldapu a pak se z remote stahne ta fotka jako dalsi entry vazana na ldap
///
/// nahore (na abstrakci) bude jen setMap() a zakladni operace
/// tedy veci jako beforeSave() a afterLoad() - to, co se ma s objektem pachat okolo (bezva pro audity)
/// oddelovac typu v aplikaci (zatim tecka, dokazu si tam ale predstavit treba # nebo |) bude v searchi - do mapperu netreba, joiny resi builder

<?php

namespace DebugsTests;


use CommonTestClass;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records;
use kalanis\kw_mapper\Search;
use kalanis\kw_mapper\Storage;


class DebugTest extends CommonTestClass
{
    public function testContentOk(): void
    {
//        $record = new PedigreeRecord();
//        $record->kennel = 'von Arlett';
//        $record->load();
//var_dump($record);

//        $record = new PedigreeRecord();
//        $record->kennel = 'Z GILANU';
//var_dump(['gl', $record->loadMultiple()]);

//        $search = new Search\Search(new PedigreeRecord());
//        $search->like('kennel', '%GILAN%');
////        $search->offset(3);
////        $search->limit(6);
//        $search->limit(2);
////var_dump(['cnt', $search->getCount()]);
//var_dump(['all', $search->getResults()]);

        try {
            $search = new Search\Search(new PedigreeUpdateRecord());
//            $search->like('kennel', '%GILAN%');
//            $search->child('parents', '', '', 'par');
            $search->child('children');
            $search->child('sires', '', 'children', 'chil');
            $search->like('chil.kennel', '%GILAN%');
//            $search->offset(3);
//            $search->limit(6);
            $search->limit(2);
//var_dump(['cnt', $search->getCount()]);
var_dump(['all', $search->getResults()]);
//print_r(['all', $search->getResults()]);

        } catch (MapperException $ex) {
            $this->assertTrue(false, $ex->getMessage());
        }

        $this->assertTrue(true);
    }
}


/**
 * Class PedigreeUpdateRecord
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string $kennel
 * @property string $birth
 * @property string $address
 * @property string $trials
 * @property string $photo
 * @property int $photoX
 * @property int $photoY
 * @property string $breed
 * @property string $sex
 * @property string $text
 * @property \DebugsTests\PedigreeRelateRecord[] $parents
 * @property \DebugsTests\PedigreeRelateRecord[] $children
 */
class PedigreeUpdateRecord extends Records\AStrictRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('key', IEntryType::TYPE_STRING, 50);
        $this->addEntry('name', IEntryType::TYPE_STRING, 75);
        $this->addEntry('kennel', IEntryType::TYPE_STRING, 255);
        $this->addEntry('birth', IEntryType::TYPE_STRING, 32);
        $this->addEntry('address', IEntryType::TYPE_STRING, 255);
        $this->addEntry('trials', IEntryType::TYPE_STRING, 255);
        $this->addEntry('photo', IEntryType::TYPE_STRING, 255);
        $this->addEntry('photoX', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('photoY', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('breed', IEntryType::TYPE_SET, ['no','yes','died']);
        $this->addEntry('sex', IEntryType::TYPE_SET, ['female','male']);
        $this->addEntry('blood', IEntryType::TYPE_SET, ['our','other']);
        $this->addEntry('text', IEntryType::TYPE_STRING, 8192);
        $this->addEntry('parents', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->addEntry('children', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper('\DebugsTests\PedigreeUpdateMapper');
    }
}


class PedigreeUpdateMapper extends Mappers\Database\ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('devel');
        $this->setTable('kal_pedigree_upd');
        $this->setRelation('id', 'kp_id');
        $this->setRelation('name', 'kp_name');
        $this->setRelation('kennel', 'kp_kennel');
        $this->setRelation('birth', 'kp_birth');
        $this->setRelation('address', 'kp_address');
        $this->setRelation('trials', 'kp_trials');
        $this->setRelation('photo', 'kp_photo');
        $this->setRelation('photoX', 'kp_photo_x');
        $this->setRelation('photoY', 'kp_photo_y');
        $this->setRelation('breed', 'kp_breed');
        $this->setRelation('sex', 'kp_sex');
        $this->setRelation('blood', 'kp_blood');
        $this->setRelation('text', 'kp_text');
        $this->addPrimaryKey('id');
        $this->addForeignKey('parents', '\DebugsTests\PedigreeRelateRecord', 'id', 'childId');
        $this->addForeignKey('children', '\DebugsTests\PedigreeRelateRecord', 'id', 'parentId');
    }
}


/**
 * Class PedigreeRelateRecord
 * @property int $id
 * @property int $childId
 * @property int $parentId
 * @property \DebugsTests\PedigreeUpdateRecord[] $parents
 * @property \DebugsTests\PedigreeUpdateRecord[] $children
 */
class PedigreeRelateRecord extends Records\AStrictRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('childId', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('parentId', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('oldes', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->addEntry('sires', IEntryType::TYPE_ARRAY); // FK - makes the array of entries every time
        $this->setMapper('\DebugsTests\PedigreeRelateMapper');
    }
}


class PedigreeRelateMapper extends Mappers\Database\ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('devel');
        $this->setTable('kal_pedigree_relate');
        $this->setRelation('id', 'kpr_id');
        $this->setRelation('childId', 'kp_id_child');
        $this->setRelation('parentId', 'kp_id_parent');
        $this->addPrimaryKey('id');
        $this->addForeignKey('oldes', '\DebugsTests\PedigreeUpdateRecord', 'parentId', 'id');
        $this->addForeignKey('sires', '\DebugsTests\PedigreeUpdateRecord', 'childId', 'id');
    }
}


/**
 * Class PedigreeRecord
 * @property string $id
 * @property string $name
 * @property string $kennel
 * @property string $birth
 * @property string $father
 * @property string $mother
 * @property string $fatherId
 * @property string $motherId
 * @property string $address
 * @property string $trials
 * @property string $photo
 * @property string $photoX
 * @property string $photoY
 * @property string $breed
 * @property string $sex
 * @property string $text
 */
class PedigreeRecord extends Records\AStrictRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_STRING, 50);
        $this->addEntry('name', IEntryType::TYPE_STRING, 75);
        $this->addEntry('kennel', IEntryType::TYPE_STRING, 255);
        $this->addEntry('birth', IEntryType::TYPE_STRING, 32);
        $this->addEntry('father', IEntryType::TYPE_STRING, 75);
        $this->addEntry('mother', IEntryType::TYPE_STRING, 75);
        $this->addEntry('fatherId', IEntryType::TYPE_STRING, 50);
        $this->addEntry('motherId', IEntryType::TYPE_STRING, 50);
        $this->addEntry('address', IEntryType::TYPE_STRING, 255);
        $this->addEntry('trials', IEntryType::TYPE_STRING, 255);
        $this->addEntry('photo', IEntryType::TYPE_STRING, 255);
        $this->addEntry('photoX', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('photoY', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('breed', IEntryType::TYPE_SET, ['no','yes','died']);
        $this->addEntry('sex', IEntryType::TYPE_SET, ['female','male']);
        $this->addEntry('blood', IEntryType::TYPE_SET, ['our','other']);
        $this->addEntry('text', IEntryType::TYPE_STRING, 8192);
        $this->setMapper('\DebugsTests\PedigreeMapper');
    }
}


class PedigreeMapper extends Mappers\Database\ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('devel');
        $this->setTable('kal_pedigree');
        $this->setRelation('id', 'id');
        $this->setRelation('name', 'name');
        $this->setRelation('kennel', 'kennel');
        $this->setRelation('birth', 'birth');
        $this->setRelation('father', 'father');
        $this->setRelation('mother', 'mother');
        $this->setRelation('fatherId', 'father_id');
        $this->setRelation('motherId', 'mother_id');
        $this->setRelation('address', 'address');
        $this->setRelation('trials', 'trials');
        $this->setRelation('photo', 'photo');
        $this->setRelation('photoX', 'photo_x');
        $this->setRelation('photoY', 'photo_y');
        $this->setRelation('breed', 'breed');
        $this->setRelation('sex', 'sex');
        $this->setRelation('blood', 'blood');
        $this->setRelation('text', 'text');
        $this->addPrimaryKey('id');
    }
}


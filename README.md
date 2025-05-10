# kw_mapper

![Build Status](https://github.com/alex-kalanis/kw_mapper/actions/workflows/code_checks.yml/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alex-kalanis/kw_mapper/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alex-kalanis/kw_mapper/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/alex-kalanis/kw_mapper/v/stable.svg?v=1)](https://packagist.org/packages/alex-kalanis/kw_mapper)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net/)
[![Downloads](https://img.shields.io/packagist/dt/alex-kalanis/kw_mapper.svg?v1)](https://packagist.org/packages/alex-kalanis/kw_mapper)
[![License](https://poser.pugx.org/alex-kalanis/kw_mapper/license.svg?v=1)](https://packagist.org/packages/alex-kalanis/kw_mapper)
[![Code Coverage](https://scrutinizer-ci.com/g/alex-kalanis/kw_mapper/badges/coverage.png?b=master&v=1)](https://scrutinizer-ci.com/g/alex-kalanis/kw_mapper/?branch=master)

Mapping records and their entries onto other object like tables or files. You can choose
from multiple sources - raw files in local or remote storage, SQL and NoSQL databases.

kw_mapper is an ORM with separated layers of mappers, records and entries - that allows
it to exchange mappers on-the-fly and target it to the different data storages like files
or databases. So it's possible to use one record to process data on more storages and yet
it will behave nearly the same way.

Basic layer is Record (like line in db) which contains Entries (like each db column with
data). The Mapper itself stays outside and tells how to translate data in Record to storage
and back.

There is many available storages:

 - MySQL/MariaDB (obviously)
 - SqLite (for smaller projects with need of sql engine)
 - Postgres (for larger projects where My is too problematic)
 - MS SQL (for commercial and things like Azure)
 - MongoDb (for SQL haters)
 - simple table in file
 - Csv file
 - Ini file
 - Yaml file
 - Json string
 - and with a little tweaking a bit more (Odbc, Dba with their connections, Oracle, Ldap, ...)

It's also possible to limit user with its input on Record level or leave him with limits on
storage. But then remember that every storage behaves differently for unwanted input!

### The main differences

What is the main differences against its competitors? At first datasources.
With competitors you can usually have only one datasource - preset database.
This can have more datasources. So usually more connections to databases.
You can have main storage in Postgres, yet authentication can run from LDAP
and ask for remote data in JSON.

Another one is the files. This mapper was build with files in mind. The file
itself behave just like another datasource. Its content can be accessed
as raw one or as another table.

With both of these things this mapper is good for transformation of data from
one storage to another.

Next one is access to raw queries only per mapper. That makes you comply with
each datasource engine separately for your customized queries. So you cannot
use the same complicated "join" query for both files and database of different
kinds.

Then here is a deep join. So you can use *Search* to access deeper stored
records in some datasources and filter by them in built query. No shallow
lookups through only relations of current record anymore! But beware that
sometimes it's better to ask more times than once in one complicated query!

Another one is in relations. Here is it an array. Always. No additional checks
or definitions if that come from 1:1, 1:N or M:N. It's an array. Period.
It can be empty, it can contain something. It's more universal than with
the definitions like oneToMany. It has been proven that this is the more
simple way. In the parent project.

## PHP Installation

```bash
composer.phar require alex-kalanis/kw_mapper
```

(Refer to [Composer Documentation](https://github.com/composer/composer/blob/master/doc/00-intro.md#introduction) if you are not
familiar with composer)


## PHP Usage

As usual, setting the storage connections and queries is more complicated than it seems.
You need to set connection to the storage and representation of parts there. This is
usually the most tedious thing, but it's necessary. Other solutions use discovery,
but that is not possible here due need to set things differently and see what it already
did. And better debug when something fails due straightforward nature of this code.
Little to no magic here.

1.) Use your autoloader (if not already done via Composer autoloader) to allow access
    to the classes.

2.) Add some external dependencies with connection to the local or remote services. Mainly
    and usually the database connections and necessary underlying libraries like PDO.

3.) Make representations of each used group. Usually tables. Note that you need to set both
    Record entity (child of ```\kalanis\kw_mapper\Records\ARecord```) and Mapper (child of
    ```\kalanis\kw_mapper\Mappers\AMapper```). Also note that both abstract classes already
    have some full/abstract children available. So choose best-suited ones and go from there.
    Not need to make all groups at once, just use the first ones need.

4.) Use Records directly or via ```\kalanis\kw_mapper\Search\Search``` library to work with
    data.

If you want to know a bit more, just open ```examples/``` directory and see the code there.
There is a small set of examples what to do and where. 

It is also possible to change mappers on Record on-the-fly. That is nice for one of variant
of transformation from one storage to another. Another variant is with different sources in
Mappers.


## Caveats

The most of the dialects for database has no limits when updating or deleting - and
roundabout way is to get sub-query with dialect-unknown primary column
by which the db will limit selection.

Another one is when you define children with the same alias - you cannot ask for
them in one query or they will mesh together and you got corrupted data. In better
case. For this case there are available children methods which allows you to define
alias to pass data when it's necessary to join from already used table. Down it
make aliases for specific columns and that allows to separate the result back into
Records.


### Possible future things

- Accessing the data across the datasources as one big bulk of data. Not like
  now when the query across the datasources will fail. As expected for now.
- Extending available datasources with its dialects
- Extending processing and coverage over the platform-specific datasources.
- Heavy DI

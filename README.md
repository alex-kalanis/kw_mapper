# kw_mapper

[![Build Status](https://travis-ci.org/alex-kalanis/kw_mapper.svg?branch=master)](https://travis-ci.org/alex-kalanis/kw_mapper)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alex-kalanis/kw_mapper/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alex-kalanis/kw_mapper/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/alex-kalanis/kw_mapper/v/stable.svg?v=1)](https://packagist.org/packages/alex-kalanis/kw_mapper)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.3-8892BF.svg)](https://php.net/)
[![Downloads](https://img.shields.io/packagist/dt/alex-kalanis/kw_mapper.svg?v1)](https://packagist.org/packages/alex-kalanis/kw_mapper)
[![License](https://poser.pugx.org/alex-kalanis/kw_mapper/license.svg?v=1)](https://packagist.org/packages/alex-kalanis/kw_mapper)
[![Code Coverage](https://scrutinizer-ci.com/g/alex-kalanis/kw_mapper/badges/coverage.png?b=master&v=1)](https://scrutinizer-ci.com/g/alex-kalanis/kw_mapper/?branch=master)

Mapping records and their entries onto other object like tables or files. you can choose
from multiple sources - raw files, SQL and NoSQL databases.

kw_mapper is a ORM with separated layers of mappers, records and entries - that allows
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
On competitors you can usually have only one datasource - preset database.
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
lookups through only relations of current record anymore!

Another one is in relations. Here is it an array. Always. No additional checks
or definitions if that come from 1:1, 1:N or M:N. It's an array. Period.
It can be empty, it can contain something. It's more universal than with
the definitions like oneToMany. It has been proven that this is the more
simple way. In the parent project.

## PHP Installation

```
{
    "require": {
        "alex-kalanis/kw_mapper": ">=2.0"
    }
}
```

(Refer to [Composer Documentation](https://github.com/composer/composer/blob/master/doc/00-intro.md#introduction) if you are not
familiar with composer)


## PHP Usage

1.) Use your autoloader (if not already done via Composer autoloader)

2.) Add some external packages with connection to the local or remote services.

3.) Connect the "kalanis\kw_mapper\Records\ARecord" into your app. Extends it for setting your case.

4.) Extend your libraries by interfaces inside the package.

5.) Just call setting and render

If you want to know more, just open ```examples/``` directory and see the code there.

## Caveats

The most of dialects for database has no limits when updating or deleting - and
roundabout way is to get sub-query with dialect-unknown primary column
by which the db will limit selection.

Another one is when you define children with the same alias - you cannot ask for
them in one query or it will mesh together and you got corrupted data. In better
case. For this case there are available children methods which allows you to define
alias to pass data when it's necessary to join from already used table. 

### Possible future things

- Accessing the data across the datasources as one big bulk of data. Not like
  now when the query across the datasources will fail. As expected.
- Extending available datasources with its dialects
- Extending processing and coverage over the platform-specific datasources.

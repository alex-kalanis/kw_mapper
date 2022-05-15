# kw_mapper

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
 - and with a little tweaking a bit more (Odbc, Dba with their connections, Oracle, Ldap, ...)

It's also possible to limit user with its input on Record level or leave him with limits on
storage. But then remember that every storage behaves differently for unwanted input!


## PHP Installation

```
{
    "require": {
        "alex-kalanis/kw_mapper": "1.0"
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

## Caveats

The most of dialects for database has no limits when updating or deleting - and
roundabout way is to get sub-query with dialect-unknown primary column
by which the db will limit selection.

Another one is when you define children with the same alias - you cannot ask for
them in one query or it will mesh together and you got corrupted data. In better
case. For this case are available children methods which allows you to define
alias to pass data when it's necessary to join from already used table. 

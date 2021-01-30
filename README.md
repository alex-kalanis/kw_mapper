# kw_mapper

Mapping records and their entries onto other object like tables or files 

# PHP Installation

```
{
    "require": {
        "alex-kalanis/kw_mapper": "dev-master"
    },
    "repositories": [
        {
            "type": "http",
            "url":  "https://github.com/alex-kalanis/kw_mapper.git"
        }
    }
}
```

(Refer to [Composer Documentation](https://github.com/composer/composer/blob/master/doc/00-intro.md#introduction) if you are not
familiar with composer)


# PHP Usage

1.) Use your autoloader (if not already done via Composer autoloader)

2.) Add some external packages with connection to the local or remote services.

3.) Connect the "kalanis\kw_mapper\Records\ARecord" into your app. Extends it for setting your case.

4.) Extend your libraries by interfaces inside the package.

5.) Just call setting and render

# Python Installation

into your "setup.py":

```
    install_requires=[
        'kw_mapper',
    ]
```

# Python Usage

1.) Connect the "kw_mapper.records" into your app. When it came necessary
you can extends every library to comply your use-case; mainly your sending agent.

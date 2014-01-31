# Sphinx Search

Sphinx Search library provides SphinxQL indexing and searching features.

[![Build Status](https://travis-ci.org/ripaclub/sphinxsearch.png?branch=develop)](https://travis-ci.org/ripaclub/sphinxsearch)

Introduction
---

This Library aims to provide:

 - A `SphinxQL` query builder based upon [Zend\Db\Sql](http://framework.zend.com/manual/2.2/en/modules/zend.db.sql.html)
 - A simple `Search` class
 - An `Indexer` class to work with RT Indexes
 - Factories for `SphinxQL` connection through [Zend\Db\Adapter](http://framework.zend.com/manual/2.2/en/modules/zend.db.adapter.html)
 
NOTE: this library doesn't not use `SphinxClient` PHP extension. Everything available via the Sphinx API is also available via `SphinxQL` but not vice versa; for instance, writing to RT indexes is only available via `SphinxQL`.

Installation
---

Using [composer](http://getcomposer.org/):

Add the following to your `composer.json` file:

    "require": {
        ...
        "ripaclub/sphinxsearch": "*",
    },
    ...
    "repositories": [
        ...
        {
            "type": "vcs",
            "url": "https://github.com/ripaclub/sphinxsearch.git"
        }
    ]

Alternately with git submodules:

    git submodule add https://github.com/ripaclub/sphinxsearch.git ripaclub/sphinxsearch


Howto
---

Register in the `ServiceManager` the provided factories through the `service_manager` configuration node:

```
'service_manager' => array(

    'factories' => array(
      'SphinxSearch\Db\Adapter\Adapter' => 'SphinxSearch\Db\Adapter\AdapterServiceFactory',
    ),

    // Alternately register the abstract facory
    'abstract_factories' => array(
      'SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory'
    ),

    // Optionally
    'aliases' => array(
      'sphinxql' => 'SphinxSearch\Db\Adapter\Adapter'
    ),

),
```

Then in your configuration add the `sphinxql` node and configure it with connection parameters via Pdo driver. Configuration parameters will be used by `Zend\Db\Adapter\Adapter` (refer to its documentation for details).

Example:

```
'sphinxql' => array(
    'driver'         => 'Pdo',
    'dsn'            => 'mysql:dbname=dummy;host=127.0.0.1;port=9306;',
    'driver_options' => array(
     \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
    ),
),
```

If you use the abstract factory refer to [Zend Db Adpater Abstract Factory documentation](http://framework.zend.com/manual/2.2/en/modules/zend.mvc.services.html#zend-db-adapter-adapterabstractservicefactory)

## Testing

Once installed development dependencies through composer you can run `phpunit`.

```{bash}
./vendor/bin/phpunit -c tests/
```


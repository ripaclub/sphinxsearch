Sphinx Search [![License](http://img.shields.io/badge/license-BSD--2-green.svg)](http://opensource.org/licenses/BSD-2-Clause)&nbsp;[![Build Status](http://img.shields.io/travis/ripaclub/sphinxsearch/develop.svg)](https://travis-ci.org/ripaclub/sphinxsearch.png?branch=develop)&nbsp;[![Latest Stable Version](https://poser.pugx.org/ripaclub/sphinxsearch/v/stable.png)](https://packagist.org/packages/ripaclub/sphinxsearch)
=============

Sphinx Search library provides SphinxQL indexing and searching features.

## Introduction


This Library aims to provide:

 - A `SphinxQL` query builder based upon [Zend\Db\Sql](http://framework.zend.com/manual/2.2/en/modules/zend.db.sql.html)
 - A simple `Search` class
 - An `Indexer` class to work with RT indices
 - Factories for `SphinxQL` connection through [Zend\Db\Adapter](http://framework.zend.com/manual/2.2/en/modules/zend.db.adapter.html)

###### Note

This library does not use `SphinxClient` PHP extension because everything available through the Sphinx API is also available via `SphinxQL` but not vice versa (i.e., writing to RT indicies is only available via `SphinxQL`).

## Installation


Using [composer](http://getcomposer.org/):

Add the following to your `composer.json` file:

    "require": {
        "php": ">=5.3.3",
        "ripaclub/sphinxsearch": "~0.2.0",
    }

Alternately with git submodules:

    git submodule add https://github.com/ripaclub/sphinxsearch.git ripaclub/sphinxsearch


## Configuration (simple)


Register in the `ServiceManager` the provided factories through the `service_manager` configuration node:

    'service_manager' => array(

        'factories' => array(
          'SphinxSearch\Db\Adapter\Adapter' => 'SphinxSearch\Db\Adapter\AdapterServiceFactory',
        ),

        // Optionally
        'aliases' => array(
          'sphinxql' => 'SphinxSearch\Db\Adapter\Adapter'
        ),

    )

Then in your configuration add the `sphinxql` node and configure it with connection parameters as in example:

    'sphinxql' => array(
        'driver'    => 'pdo_mysql',
        'database'  => 'dummy',
        'hostname'  => '127.0.0.1',
        'port'      => 9306,
        'charset'   => 'UTF8'
    )

For more details see the "Adapter Service Factory" section.

## Using



### Search


### Indexer



## Advanced


### Adapter Service Factory

This library come with two factories in bundle in order to properly configure the `Zend\Db\Adapter\Adapter` to work with Sphinx.

Use `SphinxSearch\Db\Adapter\AdapterServiceFactory` (like in the "Configuration" section above) for a single connection or, if you need to use multiple connection, use the shipped `SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory registering` it in the service manager as below:

    'service_manager' => array(
        ...
       
        'abstract_factories' => array(
          'SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory'
        ),
    )

For the abstract factory configuration refer to [Zend Db Adpater Abstract Factory documentation](http://framework.zend.com/manual/2.2/en/modules/zend.mvc.services.html#zend-db-adapter-adapterabstractservicefactory)


Only two drivers are supported:

- PDO_MySQL
- Mysqli

### Prepared statement

SphinxQL doesn't support prepared statement, but [PDO drivers are able to emulate prepared statement client side](http://it1.php.net/manual/en/pdo.prepared-statements.php). To achive prepared query benefits this library fully supports this feature. With the Pdo driver prepared and non-prepared query are supported. The Mysqli driver doesn't support prepared query.

For both `SphinxSearch\Search` and `SphinxSearch\Indexer` you can choose the working mode via `setExecutionMode()` using one of the following flags:

    const EXECUTE_MODE_PREPARED = 'prepared';
    const EXECUTE_MODE_QUERY    = 'query';
    const EXECUTE_MODE_AUTO     = 'auto';
    
With the 'auto' option the component will use the best execution mode available, prefering prepared mode if supported by the driver.


### SQL Objects




Testing
---

Once installed development dependencies through composer you can run `phpunit`.

```{bash}
./vendor/bin/phpunit -c tests/
```


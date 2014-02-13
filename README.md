Sphinx Search [![Build Status](https://travis-ci.org/ripaclub/sphinxsearch.png?branch=master)](https://travis-ci.org/ripaclub/sphinxsearch)&nbsp;[![Latest Stable Version](https://poser.pugx.org/ripaclub/sphinxsearch/v/stable.png)](https://packagist.org/packages/ripaclub/sphinxsearch)&nbsp;[![Coverage Status](https://coveralls.io/repos/ripaclub/sphinxsearch/badge.png)](https://coveralls.io/r/ripaclub/sphinxsearch)
=============

Sphinx Search library provides SphinxQL indexing and searching features.

- [Introduction](#introduction)
- [Installation](#installation)
- [Configuration (simple)](#configuration-simple)
- [Usage](#usage)
	- [Search](#search)
	- [Indexer](#indexer)
- [Advanced](#advanced)
	- [Adapter Service Factory](#adapter-service-factory)
	- [Prepared statement](#prepared-statement)
	- [Working with types](#working-with-types)
	- [SQL Objects](#sql-objects)
- [Testing](#testing)

## Introduction

This Library aims to provide:

 - A SphinxQL query builder based upon [Zend\Db\Sql](http://framework.zend.com/manual/2.2/en/modules/zend.db.sql.html)
 - A simple `Search` class
 - An `Indexer` class to work with RT indices
 - Factories for SphinxQL connection through [Zend\Db\Adapter](http://framework.zend.com/manual/2.2/en/modules/zend.db.adapter.html)

###### Note

This library does not use `SphinxClient` PHP extension because everything available through the Sphinx API is also available via SphinxQL but not vice versa (i.e., writing to RT indicies is only available via SphinxQL).

## Installation

Using [composer](http://getcomposer.org/):

Add the following to your `composer.json` file:

```json
"require": {
	"php": ">=5.3.3",
	"ripaclub/sphinxsearch": "~0.4",
}
```

Alternately with git submodules:

```bash
git submodule add https://github.com/ripaclub/sphinxsearch.git ripaclub/sphinxsearch
```

## Configuration (simple)

Register in the `ServiceManager` the provided factories through the `service_manager` configuration node:

```php
'service_manager' => array(
	'factories' => array(
  		'SphinxSearch\Db\Adapter\Adapter' => 'SphinxSearch\Db\Adapter\AdapterServiceFactory',
	),
	// Optionally
	'aliases' => array(
		'sphinxql' => 'SphinxSearch\Db\Adapter\Adapter'
	),
)
```

Then in your configuration add the `sphinxql` node and configure it with connection parameters as in example:

```php
'sphinxql' => array(
	'driver'    => 'pdo_mysql',
	'hostname'  => '127.0.0.1',
	'port'      => 9306,
	'charset'   => 'UTF8'
)
```

For more details see the "Adapter Service Factory" section.

## Usage

### Search

Assuming `$adapter` has been retrivied via `ServiceManager`:

```php
use SphinxSearch\Search;
use SphinxSearch\Db\Sql\Predicate\Match;

$search = new Search($adapter);
$rowset = $search->search('foo', new Match('?', 'ipsum dolor'));

echo 'Founds row:' . PHP_EOL;
foreach ($rowset as $row) {
	echo $row['id'] . PHP_EOL;
}
```

The `search()` method takes as first argument the index name (or an array of indicies) and the second one is the where condition (same as `Zend\Db\Sql\Select::where()`).
Furthermore `search()` second argument can accept a closure, which in turn, will be passed the current `Select` object that is being used to build the `SELECT` query.

The following usage is possible:

```php
use SphinxSearch\Search;
use SphinxSearch\Db\Sql\Select;
use SphinxSearch\Db\Sql\Predicate\Match;

$search = new Search($adapter);
$rowset = $search->search('foo', function(Select $select) {
	$select->where(new Match('?', 'ipsum dolor'))
	       ->where(array('c1 > ?' => 5))
               ->limit(1);
});
```

The `SphinxSearch\Db\Sql\Select` class (like [`Zend\Db\Sql\Select`](http://framework.zend.com/manual/2.2/en/modules/zend.db.sql.html#zend-db-sql-select) which we extend from), supports the following methods related to SQL standard clauses:

```php
$select->from($table)
$select->columns(array $columns)
$select->where($predicate, $combination = Predicate\PredicateSet::OP_AND)
$select->group($group)
$select->having($predicate, $combination = Predicate\PredicateSet::OP_AND)
$select->order($order)
$select->limit($limit)
$select->offset($offset)
// And also variable overloading for:
$select->->where
$select->->having
```

Thus it adds some SphinxQL specific methods:

```php
$select->withinGroupOrder($withinGroupOrder)
$select->option(array $values, $flag = self::OPTIONS_MERGE)
```

Other utility methods as `setSpecifications`, `getRawState` and `reset` are fully supported.

Instead `quantifier`, `join` and `combine` are just ignored because SphinxQL syntax doesn't have them.

### Indexer

Assuming `$adapter` has been retrivied via `ServiceManager` we can perform indexing of documents, provided that the indices on which we act are [real time](http://sphinxsearch.com/docs/2.2.2/rt-overview.html).

```php
use SphinxSearch\Indexer;

$indexer = new Indexer($adapter);
$indexer->insert(
	'foo',
	array(
    		'short' => 'Lorem ipsum dolor sit amet',
    		'text' => 'Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit ...'
	),
	true
);
```

Note tha the third parameter of `insert` method is a boolean flag indicating wheter a _"uspert"_ rather than an insert have to be done.

Furthermore, an `Indexer` instance allows to update and delete rows from real time indices (using the methods `update` and `delete`, respectively).

## Advanced

### Adapter Service Factory

This library come with two factories in bundle in order to properly configure the `Zend\Db\Adapter\Adapter` to work with Sphinx Search.

Use `SphinxSearch\Db\Adapter\AdapterServiceFactory` (see [Configuration](#configuration-simple) section above) for a single connection or, if you need to use multiple connection, use the shipped `SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory` registering it in the `ServiceManager` as below:

```php
'service_manager' => array(
	'abstract_factories' => array(
  		'SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory'
	),
)
```

For the abstract factory configuration refer to [Zend Db Adpater Abstract Factory documentation](http://framework.zend.com/manual/2.2/en/modules/zend.mvc.services.html#zend-db-adapter-adapterabstractservicefactory).

Only two drivers are supported:

- `PDO_MySQL`
- `Mysqli`

### Prepared statement

SphinxQL does not support prepared statement, but [PDO drivers are able to emulate prepared statement client side](http://it1.php.net/manual/en/pdo.prepared-statements.php). To achive prepared query benefits this library fully supports this feature.

###### Note
The `Pdo` driver supports prepared and non-prepared queries. The `Mysqli` driver does not support prepared queries.

For both `SphinxSearch\Search` and `SphinxSearch\Indexer` you can choose the working mode via `setQueryMode()` using one of the following flags:

```php
const QUERY_MODE_PREPARED   = 'prepared'; // use prepared statement
const QUERY_MODE_EXECUTE    = 'execute';  // do not use prepared statement
const QUERY_MODE_AUTO       = 'auto';     // auto detect best available option (prepared mode preferred)
```

With the `auto` option the component will use the best execution mode available, prefering prepared mode if supported by the driver.

### Working with types

This library aims to normalize API usage among supported drivers and modes, but due to SphinxQL limitations there are some considerations:

* `NULL`

   Not supported by SphinxQL. The library transparently handle it for SQL compatibility: an exception will be thrown by the driver if you try to use a value = `NULL`.

* `boolean`

  `SphinxQL` does not have a native boolean type, however if you try to use a PHP bool when SphinxQL expects an integer the driver will caste the value to `0` or `1` respectively.

* `integer` Both integer number and string containing integer work properly when SphinxQL expects an `uint`
  (WARNING: PHP integers are signed, instead SphinxQL supports UNSIGNED integers and UNIX timestamp)

* `float`

  Due to some limitations of PDO driver, only proper PHP float values work in prepared statement mode. Also the PDO decimal point conversion is locale aware: will work only if `LC_NUMERIC` setting is compliant with point as separator in decimal notation.

For those reasons we suggest to use proper PHP native types always (i.e., not use strings for numeric fields) when building queries.

Useful link: [Sphinx Attributes Docs](http://sphinxsearch.com/docs/current.html#attributes).

### SQL Objects

_TODO_

Testing
---

The library source code (on master) is 100% covered by unit tests.

Once installed development dependencies through composer you can run `phpunit`.

```bash
./vendor/bin/phpunit -c tests/
```

After this you can inspect stats about code coverage.

```bash
./vendor/bin/converalls -v
```


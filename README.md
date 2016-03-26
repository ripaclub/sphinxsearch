Sphinx Search
=============

[![Latest Stable Version](https://img.shields.io/packagist/v/ripaclub/sphinxsearch.svg?style=flat-square)](https://packagist.org/packages/ripaclub/sphinxsearch) [![Build Status](https://img.shields.io/travis/ripaclub/sphinxsearch/master.svg?style=flat-square)](https://travis-ci.org/ripaclub/sphinxsearch) [![Coveralls branch](https://img.shields.io/coveralls/ripaclub/sphinxsearch/master.svg?style=flat-square)](https://coveralls.io/r/ripaclub/sphinxsearch) [![Total Downloads](https://img.shields.io/packagist/dt/ripaclub/sphinxsearch.svg?style=flat-square)](https://packagist.org/packages/ripaclub/sphinxsearch)

> Sphinx Search library provides SphinxQL indexing and searching features.

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
	- [Query expression](#query-expression)
- [Testing](#testing)
- [Code quality](#code-quality)

## Introduction

This Library aims to provide:

 - A **SphinxQL query builder** based upon [Zend\Db\Sql](http://framework.zend.com/manual/2.4/en/modules/zend.db.sql.html)
 - A simple **Search** class
 - An **Indexer** class to work with RT indices
 - Factories for SphinxQL connection through [Zend\Db\Adapter](http://framework.zend.com/manual/2.4/en/modules/zend.db.adapter.html)

We have also prepared a set of **related useful tools**. You can use them in conjuction with this library.

- [ripaclub/zf2-sphinxsearch-tool](https://github.com/ripaclub/zf2-sphinxsearch-tool)

    A set of **tools** for SphinxSearch's **config files** creation and automation

- [ripaclub/zf2-sphinxsearch](https://github.com/ripaclub/zf2-sphinxsearch)

    A module for fast bootstrapping and integration of SphinxSearch library with **Zend Framework**

- [ripaclub/sphinxsearch-bundle](https://github.com/ripaclub/sphinxsearch-bundle)

    A bundle for fast bootstrapping and integration of SphinxSearch library with **Symfony**

- [leodido/sphinxsearch](https://registry.hub.docker.com/u/leodido/sphinxsearch/)
    
    SphinxSearch **docker image** (tags for various SphinxSearch's releases and betas)

###### Note

This library does not use `SphinxClient` PHP extension because everything available through the Sphinx API is also available via SphinxQL but not vice versa (i.e., writing to RT indicies is only available via SphinxQL).

## Installation

Using [composer](http://getcomposer.org/):

Add the following to your `composer.json` file:

```json
"require": {
	"ripaclub/sphinxsearch": "~0.8.0",
}
```

###### Note

Since version **0.8.1**, PHP 7 and Zend Framework's components of 3.x series are fully supported.

Starting from **0.8.x** series the minimum requirements are PHP >= 5.5 and Zend Framework dependencies >= 2.4.

When forced to use a PHP version less (or equal) than 5.4 and/or a Zend Framework dependencies less (or equal) then 2.3 you can use **0.7.1** version.

## Configuration (simple)

In order to work with library components you need an adapter instance. You can simply obtain configured adapter by using the built-in factory like the following example:

```php
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;

$serviceManagerConfig = new Config([
    'factories' => [
        'SphinxSearch\Db\Adapter\Adapter' => 'SphinxSearch\Db\Adapter\AdapterServiceFactory'
    ],
    'aliases' => [
        'sphinxql' => 'SphinxSearch\Db\Adapter\Adapter'
    ]
]);
$serviceManager = new ServiceManager();
$serviceManagerConfig->configureServiceManager($serviceManager);
$serviceManager->setService('Config', [
    'sphinxql' => [
        'driver'    => 'pdo_mysql',
        'hostname'  => '127.0.0.1',
        'port'      => 9306,
        'charset'   => 'UTF8'
    ]
]);

$adapter = $serviceManager->get('sphinxql');
```

###### Note

Only two drivers are supported:
- `pdo_mysql`
- `mysqli`

For more details see the [Adapter Service Factory](#adapter-service-factory) section.

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

The `search()` method takes as first argument the index name (or an array of indicies) and the second one accepts a where condition (same as `Zend\Db\Sql\Select::where()`).
Furthermore `search()` second argument can accept a closure, which in turn, will be passed the current `Select` object that is being used to build the `SELECT` query.

The following usage is possible:

```php
use SphinxSearch\Search;
use SphinxSearch\Db\Sql\Select;
use SphinxSearch\Db\Sql\Predicate\Match;

$search = new Search($adapter);
$rowset = $search->search('foo', function(Select $select) {
	$select->where(new Match('?', 'ipsum dolor'))
	       ->where(['c1 > ?' => 5])
               ->limit(1);
});
```

The `SphinxSearch\Db\Sql\Select` class (like [`Zend\Db\Sql\Select`](http://framework.zend.com/manual/2.4/en/modules/zend.db.sql.html#zend-db-sql-select) which we extend from) supports the following methods related to SQL standard clauses:

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
$select->where
$select->having
```

Thus it adds some SphinxQL specific methods:

```php
$select->withinGroupOrder($withinGroupOrder)
$select->option(array $values, $flag = self::OPTIONS_MERGE)
```

Other utility methods like `setSpecifications`, `getRawState` and `reset` are fully supported.

Instead `quantifier`, `join` and `combine` are just ignored because SphinxQL syntax doesn't have them.

### Indexer

Assuming `$adapter` has been retrivied via `ServiceManager` we can perform indexing of documents, provided that the indices on which we act are [real time](http://sphinxsearch.com/docs/2.2.2/rt-overview.html).

```php
use SphinxSearch\Indexer;

$indexer = new Indexer($adapter);
$indexer->insert(
	'foo',
	[
		'id' => 1,
		'short' => 'Lorem ipsum dolor sit amet',
		'text' => 'Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit ...'
	],
	true
);
```

Note that third parameter of `insert` method is a boolean flag indicating wheter a _"upsert"_ rather than an insert have to be done.

Furthermore, an `Indexer` instance allows to update and delete rows from real time indices (using the methods `update` and `delete`, respectively).

## Advanced

### Adapter Service Factory

This library come with two factories in bundle in order to properly configure the `Zend\Db\Adapter\Adapter` to work with Sphinx Search.

Use `SphinxSearch\Db\Adapter\AdapterServiceFactory` (see [Configuration](#configuration-simple) section above) for a single connection else if you need to use multiple connections use the shipped `SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory` registering it in the `ServiceManager` as below:

```php
'service_manager' => [
	'abstract_factories' => [
  		'SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory'
	],
]
```

For the abstract factory configuration refer to [Zend Db Adpater Abstract Factory documentation](http://framework.zend.com/manual/2.4/en/modules/zend.mvc.services.html#zend-db-adapter-adapterabstractservicefactory).

### Prepared statement

SphinxQL does not support prepared statement, but [PDO drivers are able to emulate prepared statement client side](http://it1.php.net/manual/en/pdo.prepared-statements.php). To achive prepared query benefits this library fully supports this feature.

###### Note
The PDO driver supports prepared and non-prepared queries. The `Mysqli` driver does not support prepared queries.

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

  SphinxQL does not have a native boolean type but if you try to use a PHP `bool` the library and the driver will cast the value to `0` or `1` respectively.


* `integer`

  PHP native integers work properly when SphinxQL expects an `uint`. Note that strings containing integers do not work in filters (i.e. `WHERE` clause).<br/>_WARNING: PHP integers are signed, instead SphinxQL supports only UNSIGNED integers and UNIX timestamp._


* `float`

    Due to SphinxQL specific issues related to `float` values (especially in `WHERE` clause), by default them are converted to a 32-bit-single-precision compatible string rappresentation which are then included into the SQL query as literals, even in the case where prepared statements are used.

    This feature works only if value is a native PHP `float` (anyway strings containing floats do not work within Sphinx). If it is needed, this behaviour can be globally disabled using `$adapter->getPlatform()->enableFloatConversion(false)`. <br/>

    _WARNING: disabling float conversion feature can produce unexpected behaviors, some notable examples:_
    - Actually Sphinx SQL interpreter treats a number without decimal part as an integer. So, assumming `f1` as float column, if you try `WHERE f1 = 10` you will get `42000 - 1064 - index foo: unsupported filter type 'intvalues' on float column` else if you try `WHERE f1 = 10.0` it will work fine.
    - Due to the fact that SphinxQL does not support float quoted as strings and PDO driver has no way to bind a double (SQL float) parameter in prepared statement mode, PDO driver will just cast to string producing a locale aware conversion (same as PHP `echo`), so it will work only if `LC_NUMERIC` setting is compliant with point as separator in decimal notation (for example you can use `LC_NUMERIC='C'`)

For those reasons we suggest to **always use proper PHP native types** (i.e., not use strings for numeric fields) when building queries.

Useful link: [Sphinx Attributes Docs](http://sphinxsearch.com/docs/current.html#attributes).

### SQL Objects

As [Zend\Db\Sql](http://framework.zend.com/manual/2.4/en/modules/zend.db.sql.html) this library provides a set of SQL objects:

* `SphinxSearch\Db\Sql\Select` explained in [Search](#search) paragraph
* `SphinxSearch\Db\Sql\Insert`
* `SphinxSearch\Db\Sql\Replace` same as insert, but overwrites duplicate IDs
* `SphinxSearch\Db\Sql\Update` with the ability to handle `OPTION` clause
* `SphinxSearch\Db\Sql\Delete`
* `SphinxSearch\Db\Sql\Show`

Each of them can be retrivied by `SphinxSearch\Db\Sql\Sql` class methods:

```php
use SphinxSearch\Db\Sql\Sql;

$sql = new Sql($adapter);
$select = $sql->select();  	// @return SphinxSearch\Db\Sql\Select
$insert = $sql->insert();   // @return SphinxSearch\Db\Sql\Insert
$insert = $sql->replace();	// @return SphinxSearch\Db\Sql\Replace
$update = $sql->update(); 	// @return SphinxSearch\Db\Sql\Update
$delete = $sql->delete();  	// @return SphinxSearch\Db\Sql\Delete
$show   = $sql->show(); 	// @return SphinxSearch\Db\Sql\Show
```

Or can be instanziated directly like in the following example:

```php
use SphinxSearch\Db\Sql\Update;
use SphinxSearch\Db\Sql\Predicate\Match;

$update = new Update;
$update->from('myindex')
       ->set(['bigattr' => 1000, 'fattr' => 3465.23])
       ->where(new Match('?', 'hehe'))
       ->where(['enabled' => 1])
       ->option('strict', 1);
```

Then you can perform your query by:

```php
$statement = $sql->prepareStatementForSqlObject($select);
$results = $statement->execute();
```

Or using the `Search` or the `Indexer` components:

```php
$resultset = $indexer->updateWith($update);
```

Thus, every object (that has `where()`) supports the `Match` expression, as explained in next paragrah.

### Query expression

The `SphinxSearch\Query\QueryExpression` class provides a placeholder expression way and a string excape mechanism in order to use safely the [Sphinx query syntax](http://sphinxsearch.com/docs/2.2.2/extended-syntax.html).
Also, the component design permits to use it standalone, since it has no dependencies on other library's components.

Some examples:

```php
use SphinxSearch\Query\QueryExpression;

$query = new QueryExpression('@title ? @body ?', ['hello', 'world']);
echo $query->toString(); //outputs: @title hello @body world


echo $query->setExpression('"?"/3')
           ->setParameters(['the world is a wonderful place, but sometimes people uses spe(ia| ch@rs'])
           ->toString(); //outputs: "the world is a wonderful place, but sometimes people uses spe\(ia\| ch\@rs"/3

echo $query->setExpression('? NEAR/? ? NEAR/? "?"')
           ->setParameters(['hello', 3, 'world', 4, '"my test"'])
           ->toString(); //outputs: hello NEAR/3 world NEAR/4 "my test"
```

The `SphinxSearch\Db\Sql\Predicate\Match` class uses internally the `QueryExpression`, so you can use it in your SQL queries directly:

```php
use SphinxSearch\Adapter\Platform\SphinxQL;
use SphinxSearch\Db\Sql\Select;
use SphinxSearch\Db\Sql\Predicate\Match;

$select = new Select;
$select->from('myindex')
       ->where(new Match('? NEAR/? ? NEAR/? "?"', ['hello', 3, 'world', 4, '"my test"']))
       ->where(['enabled' => 1]);

//outputs: SELECT * from `foo` WHERE MATCH('hello NEAR/3 world NEAR/4 "my test"') AND `enabled` = 1
echo $select->getSqlString(new SphinxQL());
```

Testing
-------

The library source code (**on master**) is 100% covered by unit tests.

Once installed development dependencies through composer you can run `phpunit`.

```
./vendor/bin/phpunit --exclude-group=integration
```

To run also our integration tests execute:

```
./vendor/bin/phpunit
```

###### Note

To execute integration tests you need a running instance of SphinxSearch (e.g., using a correctly configured docker image).

---

[![Analytics](https://ga-beacon.appspot.com/UA-49657176-3/sphinxsearch)](https://github.com/igrigorik/ga-beacon)

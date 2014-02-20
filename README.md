Sphinx Search [![Build Status](https://travis-ci.org/ripaclub/sphinxsearch.png?branch=master)](https://travis-ci.org/ripaclub/sphinxsearch)&nbsp;[![Latest Stable Version](https://poser.pugx.org/ripaclub/sphinxsearch/v/stable.png)](https://packagist.org/packages/ripaclub/sphinxsearch)&nbsp;[![Coverage Status](https://coveralls.io/repos/ripaclub/sphinxsearch/badge.png?branch=master)](https://coveralls.io/r/ripaclub/sphinxsearch?branch=master)
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
	- [Query expression](#query-expression)
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
	"ripaclub/sphinxsearch": "~0.5",
}
```

Alternately with git submodules:

```
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
	       ->where(array('c1 > ?' => 5))
               ->limit(1);
});
```

The `SphinxSearch\Db\Sql\Select` class (like [`Zend\Db\Sql\Select`](http://framework.zend.com/manual/2.2/en/modules/zend.db.sql.html#zend-db-sql-select) which we extend from) supports the following methods related to SQL standard clauses:

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
	array(
		'id' => 1,
		'short' => 'Lorem ipsum dolor sit amet',
		'text' => 'Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit ...'
	),
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
    
    This feature works only if value is a native PHP `float`. If it is needed, this behaviour can be globally disabled using `$adapter->getPlatform()->enableFloatConversion(false)`. <br/>_WARNING: disabling this feature can produce unexpected behaviors._
    
    Some notable examples:
    - Actually Sphinx SQL interpreter treats a number without decimal part as an integer. So, assumming `f1` as float column, if you try `WHERE f1 = 10` you will get `42000 - 1064 - index foo: unsupported filter type 'intvalues' on float column` else if you try `WHERE f1 = 10.0` it will work fine.
    - Due to the fact that SphinxQL does not support float quoted as strings and PDO driver has no way to bind a double (SQL float) parameter in prepared statement mode, PDO driver will just cast to string producing a locale aware conversion (same as PHP `echo`), so it will work only if `LC_NUMERIC` setting is compliant with point as separator in decimal notation (for example you can use `LC_NUMERIC='C'`)

For those reasons we suggest to **always use proper PHP native types** (i.e., not use strings for numeric fields) when building queries.

Useful link: [Sphinx Attributes Docs](http://sphinxsearch.com/docs/current.html#attributes).

### SQL Objects

As [Zend\Db\Sql](http://framework.zend.com/manual/2.2/en/modules/zend.db.sql.html) this library provides a set of SQL objects:

* `SphinxSearch\Db\Sql\Select` explained in [Search](#search) paragraph
* `SphinxSearch\Db\Sql\Insert` 
* `SphinxSearch\Db\Sql\Replace` same as insert, but overwrites duplicate IDs
* `SphinxSearch\Db\Sql\Update` with the ability to handle `OPTION` clause
* `SphinxSearch\Db\Sql\Delete`

Each of them can be retrivied by `SphinxSearch\Db\Sql\Sql` class methods:

```php
use SphinxSearch\Db\Sql\Sql;

$sql = new Sql($adapter);
$select = $sql->select();  // @return SphinxSearch\Db\Sql\Select
$insert = $sql->insert();  // @return SphinxSearch\Db\Sql\Insert
$insert = $sql->replace(); // @return SphinxSearch\Db\Sql\Insert
$update = $sql->update();  // @return SphinxSearch\Db\Sql\Update
$delete = $sql->delete();  // @return SphinxSearch\Db\Sql\Delete
```

Or can be instanziated directly like in the following example:

```php
use SphinxSearch\Db\Sql\Update;
use SphinxSearch\Db\Sql\Predicate\Match;

$update = new Update;
$update->from('myindex')
       ->set(array('bigattr' => 1000, 'fattr' => 3465.23))
       ->where(new Match('?', 'hehe'))
       ->where(array('enabled' => 1))
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

$query = new QueryExpression('@title ? @body ?', array('hello', 'world'));
echo $query->toString(); //outputs: @title hello @body world


echo $query->setExpression('"?"/3')
           ->setParameters(array('the world is a wonderful place, but sometimes people uses spe(ia| ch@rs'))
           ->toString(); //outputs: "the world is a wonderful place, but sometimes people uses spe\(ia\| ch\@rs"/3
           
echo $query->setExpression('? NEAR/? ? NEAR/? "?"')
           ->setParameters(array('hello', 3, 'world', 4, '"my test"'))
           ->toString(); //outputs: hello NEAR/3 world NEAR/4 "my test"
```

The `SphinxSearch\Db\Sql\Predicate\Match` class uses internally the `QueryExpression`, so you can use it in your SQL queries directly:

```php
use SphinxSearch\Adapter\Platform\SphinxQL;
use SphinxSearch\Db\Sql\Select;
use SphinxSearch\Db\Sql\Predicate\Match;

$select = new Select;
$select->from('myindex')
       ->where(new Match('? NEAR/? ? NEAR/? "?"', array('hello', 3, 'world', 4, '"my test"')))
       ->where(array('enabled' => 1));

//outputs: SELECT * from `foo` WHERE MATCH('hello NEAR/3 world NEAR/4 "my test"') AND `enabled` = 1       
echo $select->getSqlString(new SphinxQL()); 
```

Testing
-------

The library source code (on master) is 100% covered by unit tests.

Once installed development dependencies through composer you can run `phpunit`.

```
vendor/bin/phpunit -c tests/
```

After this you can inspect stats about code coverage.

```
vendor/bin/converalls -v
```

Code quality
------------

Run [phpmd](https://github.com/phpmd/phpmd).

```
vendor/bin/phpmd library/ text phpmd.xml
```

Run [phpcs](https://github.com/squizlabs/PHP_CodeSniffer).

```
vendor/bin/phpcs --standard=PSR2 library/
```

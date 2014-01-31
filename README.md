# Sphinx Search

Sphinx Search library provides SphinxQL indexing and searching features.

Master: [![Build Status](https://travis-ci.org/ripaclub/sphinxsearch.png?branch=master)](https://travis-ci.org/ripaclub/sphinxsearch)
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Develop: [![Build Status](https://travis-ci.org/ripaclub/sphinxsearch.png?branch=develop)](https://travis-ci.org/ripaclub/sphinxsearch)

Introduction
---

This Library aims to provide:

 - A SphinxQL query builder based upon Zend\Db\Sql ( http://framework.zend.com/manual/2.2/en/modules/zend.db.sql.html )
 - A simple Search class
 - An Indexer class to work with RT Indexes
 - Factories for SphinxQL connection via Zend\Db\Adapter 
 
NOTE: this library doesn't not use SphinxClient php extension. Everything available via the SphinxAPI is also available via SphinxQL but not vice versa; for instance, writes into RT indexes are only available via SphinxQL.

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

    git submodule add https://github.com/ripaclub/sphinxsearch.git pruno/ripaclub/sphinxsearch


## Testing

Once installed development dependencies through composer you can run `phpunit`.

```{bash}
./vendor/bin/phpunit -c tests
```


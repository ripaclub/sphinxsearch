<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\TestAsset;

use Zend\Db\Sql\AbstractSql;

/**
 * This class is intended to test where a sql object doesn't implement SqlInterface nor PreparableSqlInterface
 *
 */
class ConcreteSqlObjectAsset extends AbstractSql
{
}

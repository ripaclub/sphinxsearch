<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Sql;

use SphinxSearch\Db\Sql\Insert;
use Zend\Db\Sql\TableIdentifier;

/**
 * Class InsertTest
 */
class InsertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Insert
     */
    protected $insert;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->insert = new Insert();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers SphinxSearch\Db\Sql\Insert::into
     */
    public function testTable()
    {
        $this->insert->into('foo', 'bar');
        $this->assertEquals('foo', $this->readAttribute($this->insert, 'table'));

        $tableIdentifier = new TableIdentifier('foo', 'bar');
        $this->insert->into($tableIdentifier);
        $this->assertEquals('foo', $this->readAttribute($this->insert, 'table'));
    }
}

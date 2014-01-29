<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTests;

use SphinxSearch\Db\Sql\Sql;
use SphinxSearch\Indexer;
use SphinxSearchTests\Db\TestAsset\TrustedSphinxQL;

class IndexerTest extends \PHPUnit_Framework_TestCase
{

    protected $mockAdapter = null;

    public function setUp()
    {
        // mock the adapter, driver, and parts
        $mockResult = $this->getMock('Zend\Db\Adapter\Driver\ResultInterface');
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));
        $mockConnection = $this->getMock('Zend\Db\Adapter\Driver\ConnectionInterface');
        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockDriver->expects($this->any())->method('getConnection')->will($this->returnValue($mockConnection));
        // setup mock adapter
        $this->mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, array($mockDriver, new TrustedSphinxQL()));
    }

    /**
     * @testdox Instantiation
     */
    public function test__construct()
    {
        // constructor with only required args
        $indexer = new Indexer(
            $this->mockAdapter
        );
        $this->assertSame($this->mockAdapter, $indexer->getAdapter());
        // $this->assertInstanceOf('Zend\Db\ResultSet\ResultSet', $search->getResultSetPrototype()); // FIXME
        $this->assertInstanceOf('SphinxSearch\Db\Sql\Sql', $indexer->getSql());
        // injecting all args
        $search = new Indexer(
            $this->mockAdapter,
            $sql = new Sql($this->mockAdapter)
        );
        $this->assertSame($this->mockAdapter, $search->getAdapter());
        // $this->assertSame($resultSet, $search->getResultSetPrototype()); // FIXME
        $this->assertSame($sql, $search->getSql());
    }

}
 
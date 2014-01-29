<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTests;


use SphinxSearch\Search;
use SphinxSearch\Db\Sql\Sql;
use SphinxSearch\Db\Adapter\Platform\SphinxQL;
use Zend\Db\ResultSet\ResultSet;
class SearchTest extends \PHPUnit_Framework_TestCase
{

    protected $mockAdapter = null;

    public function setup()
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
        $this->mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, array($mockDriver, new SphinxQL()));
    }

    public function test__construct()
    {
        // constructor with only required args
        $search = new Search(
            $this->mockAdapter
        );


        $this->assertSame($this->mockAdapter, $search->getAdapter());
        $this->assertInstanceOf('Zend\Db\ResultSet\ResultSet', $search->getResultSetPrototype());
        $this->assertInstanceOf('SphinxSearch\Db\Sql\Sql', $search->getSql());

        // injecting all args
        $search = new Search(
            $this->mockAdapter,
            $resultSet = new ResultSet,
            $sql = new Sql($this->mockAdapter)
        );

        $this->assertSame($this->mockAdapter, $search->getAdapter());
        $this->assertSame($resultSet, $search->getResultSetPrototype());
        $this->assertSame($sql, $search->getSql());
    }

}

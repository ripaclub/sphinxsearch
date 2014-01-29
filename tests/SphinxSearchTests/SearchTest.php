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
use SphinxSearchTests\Db\TestAsset\TrustedSphinxQL;
use Zend\Db\ResultSet\ResultSet;
class SearchTest extends \PHPUnit_Framework_TestCase
{

    protected $mockAdapter = null;

    protected $mockSql = null;

    protected $search = null;

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


        $this->mockSql = $this->getMock('SphinxSearch\Db\Sql\Sql', array('select'), array($this->mockAdapter, 'foo'));
        $this->mockSql->expects($this->any())->method('select')->will($this->returnValue($this->getMock('SphinxSearch\Db\Sql\Select', array('where', 'getRawSate'), array('foo'))));


        // setup the search object
        $this->search = new Search($this->mockAdapter, null, $this->mockSql);
    }

    /**
     * @testdox Instantiation
     */
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


    /**
     * @covers SphinxSearch\Search::getAdapter
     */
    public function testGetAdapter()
    {
        $this->assertSame($this->mockAdapter, $this->search->getAdapter());
    }

    /**
     * @covers SphinxSearch\Search::getSql
     */
    public function testGetSql()
    {
        $this->assertInstanceOf('SphinxSearch\Db\Sql\Sql', $this->search->getSql());
    }

    /**
     * @covers SphinxSearch\Search::getResultSetPrototype
     */
    public function testGetSelectResultPrototype()
    {
        $this->assertInstanceOf('Zend\Db\ResultSet\ResultSet', $this->search->getResultSetPrototype());
    }

    /**
     * @covers SphinxSearch\Search::search
     * @covers SphinxSearch\Search::searchWith
     */
    public function testSearchWithNoWhere()
    {
        $resultSet = $this->search->search('foo');

        // check return types
        $this->assertInstanceOf('Zend\Db\ResultSet\ResultSet', $resultSet);
        $this->assertNotSame($this->search->getResultSetPrototype(), $resultSet);
    }

    /**
     * @covers SphinxSearch\Search::search
     * @covers SphinxSearch\Search::searchWith
     */
    public function testSearchWithWhereString()
    {
        $mockSelect = $this->mockSql->select();

        $mockSelect->expects($this->any())
        ->method('getRawState')
        ->will($this->returnValue(array(
            'table' => 'foo',
        ))
        );

        // assert select::from() is called
        $mockSelect->expects($this->once())
                   ->method('where')
                   ->with($this->equalTo('foo'));

        $this->search->search('foo', 'foo');
    }

    /**
     * @covers SphinxSearch\Search::search
     * @covers SphinxSearch\Search::searchWith
     */
    public function testSearchWithWhereClosure()
    {
        $mockSelect = $this->mockSql->select();

        $mockSelect->expects($this->any())
                    ->method('getRawState')
                    ->will($this->returnValue(array(
                        'table' => 'foo',
                    ))
        );

        $this->search->search('foo', function($select) use ($mockSelect) {
            self::assertSame($mockSelect, $select);
        });
    }


}

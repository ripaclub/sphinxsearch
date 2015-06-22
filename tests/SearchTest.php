<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest;

use SphinxSearch\Db\Sql\Show;
use SphinxSearch\Db\Sql\Sql;
use SphinxSearch\Search;
use SphinxSearchTest\Db\TestAsset\TrustedSphinxQL;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

/**
 * Class SearchTest
 */
class SearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Adapter
     */
    protected $mockAdapter = null;

    /**
     * @var Sql
     */
    protected $mockSql = null;

    /**
     * @var ResultInterface
     */
    protected $mockResult = null;

    /**
     * @var Search
     */
    protected $search = null;

    public function setUp()
    {
        // mock the adapter, driver, and parts
        $this->mockResult = $this->getMock('\Zend\Db\Adapter\Driver\ResultInterface');

        $mockStatement = $this->getMock('\Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($this->mockResult));
        $mockConnection = $this->getMock('\Zend\Db\Adapter\Driver\ConnectionInterface');
        $mockConnection->expects($this->any())->method('execute')->will($this->returnValue($this->mockResult));

        $mockDriver = $this->getMock('\Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockDriver->expects($this->any())->method('getConnection')->will($this->returnValue($mockConnection));

        // setup mock adapter
        $this->mockAdapter = $this->getMock(
            '\Zend\Db\Adapter\Adapter',
            null,
            [$mockDriver, new TrustedSphinxQL()]
        );

        $this->mockSql = $this->getMock(
            '\SphinxSearch\Db\Sql\Sql',
            ['select', 'show'],
            [$this->mockAdapter, 'foo']
        );
        $this->mockSql->expects($this->any())->method('select')->will(
            $this->returnValue(
                $this->getMock('\SphinxSearch\Db\Sql\Select', ['where', 'getRawSate'], ['foo'])
            )
        );

        $mockShow = $this->getMock('\SphinxSearch\Db\Sql\Show');
        $mockShow->expects($this->any())->method('show')->will($this->returnSelf());
        $mockShow->expects($this->any())->method('like')->will($this->returnSelf());
        $this->mockSql->expects($this->any())->method('show')->will($this->returnValue($mockShow));

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
        $this->assertInstanceOf('\Zend\Db\ResultSet\ResultSet', $search->getResultSetPrototype());
        $this->assertInstanceOf('\SphinxSearch\Db\Sql\Sql', $search->getSql());

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
        $this->assertInstanceOf('\SphinxSearch\Db\Sql\Sql', $this->search->getSql());
    }

    /**
     * @covers SphinxSearch\Search::getResultSetPrototype
     */
    public function testGetSelectResultPrototype()
    {
        $this->assertInstanceOf('\Zend\Db\ResultSet\ResultSet', $this->search->getResultSetPrototype());
    }

    /**
     * @covers SphinxSearch\Search::search
     * @covers SphinxSearch\Search::searchWith
     */
    public function testSearchWithNoWhere()
    {
        $resultSet = $this->search->search('foo');

        // check return types
        $this->assertInstanceOf('\Zend\Db\ResultSet\ResultSet', $resultSet);
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
            ->will(
                $this->returnValue(
                    [
                        'table' => 'foo',
                    ]
                )
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
            ->will(
                $this->returnValue(
                    [
                        'table' => 'foo',
                    ]
                )
            );

        $this->search->search(
            'foo',
            function ($select) use ($mockSelect) {
                SearchTest::assertSame($mockSelect, $select);
            }
        );
    }

    /**
     * @covers SphinxSearch\Search::showMeta
     */
    public function testShowMeta()
    {
        $mockShow = $this->mockSql->show();
        $mockShow->expects($this->once())
            ->method('show')
            ->with($this->equalTo(Show::SHOW_META));
        $mockShow->expects($this->once())
            ->method('like')
            ->with($this->equalTo('tot'));

        // Assumes prepared statement
        $this->mockResult->expects($this->at(0))->method('rewind')->will($this->returnValue(true));
        $this->mockResult->expects($this->at(1))->method('valid')->will($this->returnValue(true));
        $this->mockResult->expects($this->at(2))->method('current')->will(
            $this->returnValue(
                ['Variable_name' => 'total', 'Value' => '0']
            )
        );
        $this->mockResult->expects($this->at(3))->method('next');
        $this->mockResult->expects($this->at(4))->method('valid')->will($this->returnValue(false));

        $result = $this->search->showMeta('tot');
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertEquals('0', $result['total']);
    }

//    public function testShowWarnings()
//    {
//        $mockShow = $this->mockSql->show();
//        $mockShow->expects($this->once())
//            ->method('show')
//            ->with($this->equalTo(Show::SHOW_WARNINGS));
//
//        // Assumes prepared statement
//        $expected = ['Level' => 'warning', 'Code' => '1000', 'Message' => 'quorum threshold'];
//        $this->mockResult->expects($this->at(0))->method('rewind')->will($this->returnValue(true));
//        $this->mockResult->expects($this->at(1))->method('valid')->will($this->returnValue(true));
//        $this->mockResult->expects($this->at(2))->method('current')->will($this->returnValue($expected));
//        $this->mockResult->expects($this->at(3))->method('next');
//        $this->mockResult->expects($this->at(4))->method('valid')->will($this->returnValue(false));
//
//        $result = $this->search->showWarnings();
//        $this->assertInternalType('array', $result);
//        $this->assertCount(1, $result);
//        $this->assertEquals([$expected], $result);
//    }

    /**
     * @covers SphinxSearch\Search::showStatus
     * @covers SphinxSearch\Search::show
     */
    public function testShowStatus()
    {
        $mockShow = $this->mockSql->show();
        $mockShow->expects($this->once())
            ->method('show')
            ->with($this->equalTo(Show::SHOW_STATUS));
        $mockShow->expects($this->once())
            ->method('like')
            ->with($this->equalTo('up%'));

        // Assumes prepared statement
        $this->mockResult->expects($this->at(0))->method('rewind')->will($this->returnValue(true));
        $this->mockResult->expects($this->at(1))->method('valid')->will($this->returnValue(true));
        $this->mockResult->expects($this->at(2))->method('current')->will(
            $this->returnValue(
                ['Counter' => 'uptime', 'Value' => '1392']
            )
        );
        $this->mockResult->expects($this->at(3))->method('next');
        $this->mockResult->expects($this->at(4))->method('valid')->will($this->returnValue(false));

        $result = $this->search->showStatus('up%');
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('uptime', $result);
        $this->assertEquals('1392', $result['uptime']);
    }
}

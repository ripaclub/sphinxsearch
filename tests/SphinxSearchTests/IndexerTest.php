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

    protected $mockSql = null;

    /**
     * @var Indexer
     */
    protected $indexer = null;

    public function setUp()
    {
        // mock the adapter, driver, and parts
        $mockResult = $this->getMock('Zend\Db\Adapter\Driver\ResultInterface');
        $mockResult->expects($this->any())->method('getAffectedRows')->will($this->returnValue(5));

        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));

        $mockConnection = $this->getMock('Zend\Db\Adapter\Driver\ConnectionInterface');
        $mockConnection->expects($this->any())->method('getLastGeneratedValue')->will($this->returnValue(10));

        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockDriver->expects($this->any())->method('getConnection')->will($this->returnValue($mockConnection));

        // setup mock adapter
        $this->mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, array($mockDriver, new TrustedSphinxQL()));

        $this->mockSql = $this->getMock('SphinxSearch\Db\Sql\Sql', array('select', 'insert', 'replace', 'update', 'delete'), array($this->mockAdapter, 'foo'));
        $this->mockSql->expects($this->any())->method('select')->will($this->returnValue($this->getMock('SphinxSearch\Db\Sql\Select', array('where', 'getRawSate'), array('foo'))));
        $this->mockSql->expects($this->any())->method('insert')->will($this->returnValue($this->getMock('Zend\Db\Sql\Insert', array('prepareStatement', 'values'), array('foo'))));
        $this->mockSql->expects($this->any())->method('replace')->will($this->returnValue($this->getMock('SphinxSearch\Db\Sql\Replace', array('prepareStatement', 'values'), array('foo'))));
        $this->mockSql->expects($this->any())->method('update')->will($this->returnValue($this->getMock('SphinxSearch\Db\Sql\Update', array('where'), array('foo'))));
        $this->mockSql->expects($this->any())->method('delete')->will($this->returnValue($this->getMock('Zend\Db\Sql\Delete', array('where'), array('foo'))));

        // setup the indexer object
        $this->indexer = new Indexer($this->mockAdapter, $this->mockSql);
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
        $this->assertInstanceOf('SphinxSearch\Db\Sql\Sql', $indexer->getSql());
        // injecting all args
        $indexer = new Indexer(
            $this->mockAdapter,
            $sql = new Sql($this->mockAdapter)
        );
        $this->assertSame($this->mockAdapter, $indexer->getAdapter());
        $this->assertSame($sql, $indexer->getSql());
    }

    /**
     * @covers SphinxSearch\Indexer::getAdapter
     */
    public function testGetAdapter()
    {
        $this->assertSame($this->mockAdapter, $this->indexer->getAdapter());
    }

    /**
     * @covers SphinxSearch\Indexer::getSql
     */
    public function testGetSql()
    {
        $this->assertInstanceOf('Zend\Db\Sql\Sql', $this->indexer->getSql());
    }

    /**
     * @covers SphinxSearch\Indexer::insert
     * @covers SphinxSearch\Indexer::insertWith
     */
    public function testInsert()
    {
        $mockInsert = $this->mockSql->insert();

        $mockInsert->expects($this->once())
        ->method('prepareStatement')
        ->with($this->mockAdapter);


        $mockInsert->expects($this->once())
        ->method('values')
        ->with($this->equalTo(array('foo' => 'bar')));

        $affectedRows = $this->indexer->insert('foo', array('foo' => 'bar'));
        $this->assertEquals(5, $affectedRows);

        // Testing Replace mode

        $mockReplace = $this->mockSql->replace();

        $mockReplace->expects($this->once())
        ->method('prepareStatement')
        ->with($this->mockAdapter);


        $mockReplace->expects($this->once())
        ->method('values')
        ->with($this->equalTo(array('foo' => 'bar')));

        $affectedRows = $this->indexer->insert('foo', array('foo' => 'bar'), true);
        $this->assertEquals(5, $affectedRows);
    }


}

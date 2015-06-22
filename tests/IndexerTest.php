<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest;

use SphinxSearch\Db\Sql\Sql;
use SphinxSearch\Indexer;
use SphinxSearchTest\Db\TestAsset\TrustedSphinxQL;

/**
 * Class IndexerTest
 */
class IndexerTest extends \PHPUnit_Framework_TestCase
{
    protected $mockAdapter = null;

    /**
     * @var Sql
     */
    protected $mockSql = null;

    /**
     * @var Indexer
     */
    protected $indexer = null;

    public function setUp()
    {
        // Mock the adapter, driver, and parts
        $mockResult = $this->getMock('\Zend\Db\Adapter\Driver\ResultInterface');
        $mockResult->expects($this->any())->method('getAffectedRows')->will($this->returnValue(5));

        $mockStatement = $this->getMock('\Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));

        $mockConnection = $this->getMock('\Zend\Db\Adapter\Driver\ConnectionInterface');
        $mockConnection->expects($this->any())->method('beginTransaction');
        $mockConnection->expects($this->any())->method('commit');
        $mockConnection->expects($this->any())->method('rollback');

        $mockDriver = $this->getMock('\Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockDriver->expects($this->any())->method('getConnection')->will($this->returnValue($mockConnection));

        // Setup mock adapter
        $this->mockAdapter = $this->getMock(
            '\Zend\Db\Adapter\Adapter',
            null,
            [$mockDriver, new TrustedSphinxQL()]
        );

        $this->mockSql = $this->getMock(
            '\SphinxSearch\Db\Sql\Sql',
            ['insert', 'replace', 'update', 'delete'],
            [$this->mockAdapter]
        );

        $this->mockSql->expects($this->any())->method('insert')->will(
            $this->returnValue($this->getMock('\Zend\Db\Sql\Insert', ['prepareStatement', 'values']))
        )->with($this->equalTo('foo'));
        $this->mockSql->expects($this->any())->method('replace')->will(
            $this->returnValue($this->getMock('\SphinxSearch\Db\Sql\Replace', ['prepareStatement', 'values']))
        )->with($this->equalTo('foo'));
        $this->mockSql->expects($this->any())->method('update')->will(
            $this->returnValue($this->getMock('\SphinxSearch\Db\Sql\Update', ['where']))
        )->with($this->equalTo('foo'));
        $this->mockSql->expects($this->any())->method('delete')->will(
            $this->returnValue($this->getMock('\Zend\Db\Sql\Delete', ['where']))
        )->with($this->equalTo('foo'));

        // Setup the indexer object
        $this->indexer = new Indexer($this->mockAdapter, $this->mockSql);
    }

    /**
     * @testdox Instantiation
     */
    public function test__construct()
    {
        // Constructor with only required args
        $indexer = new Indexer(
            $this->mockAdapter
        );
        $this->assertSame($this->mockAdapter, $indexer->getAdapter());
        $this->assertInstanceOf('\SphinxSearch\Db\Sql\Sql', $indexer->getSql());
        // Injecting all args
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
        $this->assertInstanceOf('\Zend\Db\Sql\Sql', $this->indexer->getSql());
    }

    /**
     * @covers SphinxSearch\Indexer::beginTransaction
     */
    public function testBeginTransaction()
    {
        $this->mockAdapter->getDriver()->getConnection()->expects($this->once())->method('beginTransaction');
        $indexer = $this->indexer->beginTransaction();
        $this->assertInstanceOf('\SphinxSearch\Indexer', $indexer);
    }

    /**
     * @covers SphinxSearch\Indexer::commit
     */
    public function testCommit()
    {
        $this->mockAdapter->getDriver()->getConnection()->expects($this->once())->method('commit');
        $indexer = $this->indexer->commit();
        $this->assertInstanceOf('\SphinxSearch\Indexer', $indexer);
    }

    /**
     * @covers SphinxSearch\Indexer::rollback
     */
    public function testRollback()
    {
        $this->mockAdapter->getDriver()->getConnection()->expects($this->once())->method('rollback');
        $indexer = $this->indexer->rollback();
        $this->assertInstanceOf('\SphinxSearch\Indexer', $indexer);
    }

    /**
     * @covers SphinxSearch\Indexer::insert
     * @covers SphinxSearch\Indexer::insertWith
     */
    public function testInsert()
    {
        $mockInsert = $this->mockSql->insert('foo');

        $mockInsert->expects($this->once())
            ->method('prepareStatement')
            ->with($this->mockAdapter);

        $mockInsert->expects($this->once())
            ->method('values')
            ->with($this->equalTo(['foo' => 'bar']));

        $affectedRows = $this->indexer->insert('foo', ['foo' => 'bar']);
        $this->assertEquals(5, $affectedRows);

        // Testing replace mode
        $mockReplace = $this->mockSql->replace('foo');

        $mockReplace->expects($this->once())
            ->method('prepareStatement')
            ->with($this->mockAdapter);

        $mockReplace->expects($this->once())
            ->method('values')
            ->with($this->equalTo(['foo' => 'bar']));

        $affectedRows = $this->indexer->insert('foo', ['foo' => 'bar'], true);
        $this->assertEquals(5, $affectedRows);
    }

    /**
     * @covers SphinxSearch\Indexer::update
     * @covers SphinxSearch\Indexer::updateWith
     */
    public function testUpdate()
    {
        $mockUpdate = $this->mockSql->update('foo');

        $mockUpdate->expects($this->once())
            ->method('where')
            ->with($this->equalTo('id = 2'));

        $affectedRows = $this->indexer->update('foo', ['foo' => 'bar'], 'id = 2');
        $this->assertEquals(5, $affectedRows);

        // Where with closure
        $mockUpdate = $this->mockSql->update('foo');
        $this->indexer->update(
            'foo',
            ['foo' => 'bar'],
            function ($update) use ($mockUpdate) {
                IndexerTest::assertSame($mockUpdate, $update);
            }
        );
        $this->assertEquals(5, $affectedRows);
    }

    /**
     * @covers SphinxSearch\Indexer::update
     * @covers SphinxSearch\Indexer::updateWith
     */
    public function testUpdateWithNoCriteria()
    {
        $affectedRows = $this->indexer->update('foo', ['foo' => 'bar']);
        $this->assertEquals(5, $affectedRows);
    }

    /**
     * @covers SphinxSearch\Indexer::delete
     * @covers SphinxSearch\Indexer::deleteWith
     */
    public function testDelete()
    {
        $mockDelete = $this->mockSql->delete('foo');

        $mockDelete->expects($this->once())
            ->method('where')
            ->with($this->equalTo('id = 2'));

        $affectedRows = $this->indexer->delete('foo', 'id = 2');
        $this->assertEquals(5, $affectedRows);

        // Where with closure
        $mockDelete = $this->mockSql->delete('foo');
        $this->indexer->delete(
            'foo',
            function ($delete) use ($mockDelete) {
                IndexerTest::assertSame($mockDelete, $delete);
            }
        );
    }
}

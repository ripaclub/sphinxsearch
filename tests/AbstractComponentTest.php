<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest;

use SphinxSearch\AbstractComponent;
use SphinxSearch\Db\Sql\Select;
use SphinxSearch\Db\Sql\Sql;
use SphinxSearchTest\Db\TestAsset\TrustedSphinxQL;
use SphinxSearchTest\TestAsset\ConcreteComponentAsset;

/**
 * Class AbstractComponentTest
 */
class AbstractComponentTest extends \PHPUnit_Framework_TestCase
{
    protected $mockAdapter = null;

    /**
     * @var Sql
     */
    protected $mockSql = null;

    /**
     * @var AbstractComponent
     */
    protected $component = null;

    protected $mockStatement = null;

    protected $mockConnection = null;

    public function setUp()
    {
        // mock the adapter, driver, and parts
        $mockResult = $this->getMock('\Zend\Db\Adapter\Driver\ResultInterface');
        $mockStatement = $this->getMock('\Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));
        $this->mockStatement = $mockStatement;
        $mockConnection = $this->getMock('\Zend\Db\Adapter\Driver\ConnectionInterface');
        $mockConnection->expects($this->any())->method('execute')->will($this->returnValue($mockResult));
        $this->mockConnection = $mockConnection;
        $mockDriver = $this->getMock('\Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockDriver->expects($this->any())->method('getConnection')->will($this->returnValue($mockConnection));
        $mockResultSet = $this->getMock('\Zend\Db\ResultSet\ResultSet');

        // setup mock adapter
        $this->mockAdapter = $this->getMock(
            '\Zend\Db\Adapter\Adapter',
            ['getDriver', 'query'],
            [$mockDriver, new TrustedSphinxQL()]
        );
        $this->mockAdapter->expects($this->any())->method('getDriver')->will($this->returnValue($mockDriver));
        $this->mockAdapter->expects($this->any())->method('query')->will($this->returnValue($mockResultSet));

        $this->mockSql = $this->getMock(
            '\SphinxSearch\Db\Sql\Sql',
            ['prepareStatementForSqlObject', 'getSqlStringForSqlObject'],
            [$this->mockAdapter, 'foo']
        );
        $this->mockSql->expects($this->any())->method('prepareStatementForSqlObject')->will(
            $this->returnValue($this->mockStatement)
        );
        $this->mockSql->expects($this->any())->method('getSqlStringForSqlObject')->will(
            $this->returnValue('SQL STRING')
        );

        // setup the object
        $this->component = new ConcreteComponentAsset($this->mockAdapter, $this->mockSql);
    }


    /**
     * @covers SphinxSearch\AbstractComponent::getAdapter
     */
    public function testGetAdapter()
    {
        $this->assertSame($this->mockAdapter, $this->component->getAdapter());
    }

    /**
     * @covers SphinxSearch\AbstractComponent::getSql
     */
    public function testGetSql()
    {
        $this->assertInstanceOf('\SphinxSearch\Db\Sql\Sql', $this->component->getSql());
    }

    /**
     * @covers SphinxSearch\AbstractComponent::getQueryMode
     */
    public function testDefaultQueryMode()
    {
        $this->assertEquals(AbstractComponent::QUERY_MODE_AUTO, $this->component->getQueryMode());
    }

    /**
     * @covers  SphinxSearch\AbstractComponent::setQueryMode
     * @depends testDefaultQueryMode
     */
    public function testSetQueryMode()
    {
        $this->component->setQueryMode(AbstractComponent::QUERY_MODE_EXECUTE);
        $this->assertEquals(AbstractComponent::QUERY_MODE_EXECUTE, $this->component->getQueryMode());

        $this->component->setQueryMode(AbstractComponent::QUERY_MODE_PREPARED);
        $this->assertEquals(AbstractComponent::QUERY_MODE_PREPARED, $this->component->getQueryMode());

        $this->component->setQueryMode(AbstractComponent::QUERY_MODE_AUTO);
        $this->assertEquals(AbstractComponent::QUERY_MODE_AUTO, $this->component->getQueryMode());

        $this->setExpectedException('InvalidArgumentException');

        $this->component->setQueryMode('invalid mode');
    }

    /**
     * @covers  SphinxSearch\AbstractComponent::isPreparedStatementUsed
     * @depends testSetQueryMode
     */
    public function testGetUsePreparedStatement()
    {
        $this->component->setQueryMode(AbstractComponent::QUERY_MODE_EXECUTE);
        $this->assertEquals(false, $this->component->isPreparedStatementUsed());

        $this->component->setQueryMode(AbstractComponent::QUERY_MODE_PREPARED);
        $this->assertEquals(true, $this->component->isPreparedStatementUsed());

        //Assuming generic mock driver
        $this->component->setQueryMode(AbstractComponent::QUERY_MODE_AUTO);
        $this->assertEquals(true, $this->component->isPreparedStatementUsed());


        // testing Mysqli driver
        $mockDriver = $this->getMock('\Zend\Db\Adapter\Driver\Mysqli\Mysqli', [], [], '', false);

        // setup mock adapter
        $mysqliMockAdapter = $this->getMock('\Zend\Db\Adapter\Adapter', null, [$mockDriver]);

        // setup the object
        $component = new ConcreteComponentAsset($mysqliMockAdapter);

        $component->setQueryMode(AbstractComponent::QUERY_MODE_AUTO);
        $this->assertEquals(false, $component->isPreparedStatementUsed());
    }

    /**
     * @covers  SphinxSearch\AbstractComponent::executeSqlObject
     * @depends testGetUsePreparedStatement
     */
    public function testExecuteSqlObject()
    {
        $sqlObj = new Select('foo');

        $this->component->setQueryMode(AbstractComponent::QUERY_MODE_PREPARED);
        $this->mockSql->expects($this->at(0))->method('prepareStatementForSqlObject')->with($this->equalTo($sqlObj));

        $result = $this->component->executeSqlObject($sqlObj);
        $this->assertInstanceOf('\Zend\Db\Adapter\Driver\ResultInterface', $result);


        $this->component->setQueryMode(AbstractComponent::QUERY_MODE_EXECUTE);
        $this->mockSql->expects($this->at(0))->method('getSqlStringForSqlObject')->with($this->equalTo($sqlObj));

        $this->mockConnection->expects($this->at(0))->method('execute')->with($this->equalTo('SQL STRING'));

        $result = $this->component->executeSqlObject($sqlObj);
        $this->assertInstanceOf('\Zend\Db\Adapter\Driver\ResultInterface', $result);
    }

    /**
     * @covers  SphinxSearch\AbstractComponent::executeSqlObject
     * @depends testExecuteSqlObject
     */
    public function testExecuteSqlObjectWithSecondArg()
    {
        $sqlObj = new Select('foo');

        $this->component->setQueryMode(AbstractComponent::QUERY_MODE_EXECUTE);
        $this->mockSql->expects($this->at(0))->method('prepareStatementForSqlObject')->with($this->equalTo($sqlObj));

        $result = $this->component->executeSqlObject($sqlObj, true); //force prepared, ignoring query mode
        $this->assertInstanceOf('\Zend\Db\Adapter\Driver\ResultInterface', $result);


        $this->component->setQueryMode(AbstractComponent::QUERY_MODE_PREPARED);
        $this->mockSql->expects($this->at(0))->method('getSqlStringForSqlObject')->with($this->equalTo($sqlObj));

        $this->mockConnection->expects($this->at(0))->method('execute')->with($this->equalTo('SQL STRING'));

        $result = $this->component->executeSqlObject($sqlObj, false); //force execute, ignoring query mode
        $this->assertInstanceOf('\Zend\Db\Adapter\Driver\ResultInterface', $result);
    }

    /**
     * @covers SphinxSearch\AbstractComponent::execute
     */
    public function testExecute()
    {
        $this->mockConnection->expects($this->at(0))->method('execute')->with($this->equalTo('SHOW META'));
        $result = $this->component->execute('SHOW META');
        $this->assertInstanceOf('\Zend\Db\Adapter\Driver\ResultInterface', $result);
    }
}

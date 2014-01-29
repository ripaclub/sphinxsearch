<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTests\Db\Sql;



use SphinxSearch\Db\Sql\Sql;

class SqlTest extends \PHPUnit_Framework_TestCase
{

    protected $mockAdapter = null;

    /**
     * Sql object
     * @var Sql
     */
    protected $sql = null;

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
        $this->mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, array($mockDriver));

        $this->sql = new Sql($this->mockAdapter, 'foo'); // FIXME: append SphinxQL platform?
    }

    /**
     * @covers SphinxSearch\Db\Sql\Sql::select
     */
    public function testSelect()
    {
        $select = $this->sql->select();
        $this->assertInstanceOf('SphinxSearch\Db\Sql\Select', $select);
        $this->assertSame('foo', $select->getRawState('table'));

        $this->setExpectedException(
            'SphinxSearch\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "foo" provided at construction time.');
        $this->sql->select('bar');
    }

}

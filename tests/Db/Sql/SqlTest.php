<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Sql;

use SphinxSearch\Db\Sql\Sql;
use SphinxSearchTest\Db\TestAsset\TrustedSphinxQL;
use Zend\Db\Adapter\Adapter;

/**
 * Class SqlTest
 */
class SqlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Adapter
     */
    protected $mockAdapter = null;

    /**
     * Sql object
     *
     * @var Sql
     */
    protected $sql = null;

    public function setup()
    {
        // mock the adapter, driver, and parts
        $mockResult = $this->getMock('\Zend\Db\Adapter\Driver\ResultInterface');
        $mockStatement = $this->getMock('\Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));
        $mockConnection = $this->getMock('\Zend\Db\Adapter\Driver\ConnectionInterface');
        $mockDriver = $this->getMock('\Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockDriver->expects($this->any())->method('getConnection')->will($this->returnValue($mockConnection));
        // setup mock adapter
        $this->mockAdapter = $this->getMock(
            '\Zend\Db\Adapter\Adapter',
            null,
            [$mockDriver, new TrustedSphinxQL()]
        ); // FIXME: give here the platform?

        $this->sql = new Sql($this->mockAdapter, 'foo'); // FIXME: append SphinxQL platform as 3 parameter ?
    }

    /**
     * @covers SphinxSearch\Db\Sql\Sql::select
     */
    public function testSelect()
    {
        $select = $this->sql->select();
        $this->assertInstanceOf('\SphinxSearch\Db\Sql\Select', $select);
        $this->assertSame('foo', $select->getRawState('table'));

        $this->setExpectedException(
            '\SphinxSearch\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "foo" provided at construction time.'
        );
        $this->sql->select('bar');
    }

    /**
     * @covers SphinxSearch\Db\Sql\Sql::insert
     */
    public function testInsert()
    {
        $insert = $this->sql->insert();
        $this->assertInstanceOf('\SphinxSearch\Db\Sql\Insert', $insert);
        $this->assertSame('foo', $insert->getRawState('table'));

        $this->setExpectedException(
            '\SphinxSearch\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "foo" provided at construction time.'
        );
        $this->sql->insert('bar');
    }

    /**
     * @covers SphinxSearch\Db\Sql\Sql::replace
     */
    public function testReplace()
    {
        $insert = $this->sql->replace();
        $this->assertInstanceOf('\SphinxSearch\Db\Sql\Replace', $insert);
        $this->assertSame('foo', $insert->getRawState('table'));

        $this->setExpectedException(
            '\SphinxSearch\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "foo" provided at construction time.'
        );
        $this->sql->replace('bar');
    }

    /**
     * @covers SphinxSearch\Db\Sql\Sql::update
     */
    public function testUpdate()
    {
        $update = $this->sql->update();
        $this->assertInstanceOf('\Zend\Db\Sql\Update', $update);
        $this->assertSame('foo', $update->getRawState('table'));

        $this->setExpectedException(
            '\SphinxSearch\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "foo" provided at construction time.'
        );
        $this->sql->update('bar');
    }

    /**
     * @covers SphinxSearch\Db\Sql\Sql::delete
     */
    public function testDelete()
    {
        $delete = $this->sql->delete();
        $this->assertInstanceOf('\Zend\Db\Sql\Delete', $delete);
        $this->assertSame('foo', $delete->getRawState('table'));

        $this->setExpectedException(
            '\SphinxSearch\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "foo" provided at construction time.'
        );
        $this->sql->delete('bar');
    }

    /**
     * @covers SphinxSearch\Db\Sql\Sql::show
     */
    public function testShow()
    {
        $show = $this->sql->show();
        $this->assertInstanceOf('\SphinxSearch\Db\Sql\Show', $show);
    }
}

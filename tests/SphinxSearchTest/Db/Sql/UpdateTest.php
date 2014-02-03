<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Sql;

use SphinxSearch\Db\Sql\Update;
use SphinxSearchTest\Db\TestAsset\TrustedSphinxQL;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\TableIdentifier;

class UpdateTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Update
     */
    protected $update;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->update = new Update;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers SphinxSearch\Db\Sql\Update::getRawState
     */
    public function testGetRawState()
    {
        $this->update->table('foo')
            ->set(array('bar' => 'baz'))
            ->where('x = y')
            ->option(array('ranker' => 'bm25'));

        $this->assertEquals('foo', $this->update->getRawState('table'));
        $this->assertEquals(true, $this->update->getRawState('emptyWhereProtection'));
        $this->assertEquals(array('bar' => 'baz'), $this->update->getRawState('set'));
        $this->assertEquals(array('ranker' => 'bm25'), $this->update->getRawState('option')); // FIXME: option
        $this->assertInstanceOf('Zend\Db\Sql\Where', $this->update->getRawState('where'));
    }

    /**
     * @testdox Method option() returns same Update object (is chainable)
     * @covers SphinxSearch\Db\Sql\Update::option
     */
    public function testOption()
    {
        $update = new Update;
        $return = $update->option(array('opt_name' => 'opt_value'));
        $return = $update->option(array('opt_name2' => 'opt_value2'));
        $this->assertSame($update, $return);

        return $return;
    }

    /**
     * @testdox Method getRawState() returns information populated via option()
     * @covers SphinxSearch\Db\Sql\Update::getRawState
     * @depends testOption
     */
    public function testGetRawOption(Update $update)
    {
        $this->assertEquals(
            array('opt_name' => 'opt_value', 'opt_name2' => 'opt_value2'),
            $update->getRawState('option')
        );

        return $update;
    }

    /**
     * @testdox Method option() with OPTIONS_SET flag
     * @covers SphinxSearch\Db\Sql\Update::option
     * @covers SphinxSearch\Db\Sql\Update::getRawState
     * @depends testGetRawOption
     */
    public function testOptionSet(Update $update)
    {
        $update->option(array('opt_name3' => 'opt_value3'), $update::OPTIONS_SET);
        $this->assertEquals(
            array('opt_name3' => 'opt_value3'),
            $update->getRawState('option')
        );
    }

    /**
     * @testdox Method option() launch exception with null values
     * @expectedException SphinxSearch\Db\Sql\Exception\InvalidArgumentException
     * @depends testGetRawOption
     */
    public function testNullOptionValues(Update $update)
    {
        $update->option(array());
    }

    /**
     * @testdox Method option() launch exception when value keys are not strings
     * @expectedException SphinxSearch\Db\Sql\Exception\InvalidArgumentException
     * @depends testGetRawOption
     */
    public function testNotStringOptionValueKeys(Update $update)
    {
        $update->option(array(1 => 'opt_values4'));
    }

    /**
     * @covers SphinxSearch\Db\Sql\Update::prepareStatement
     * @covers SphinxSearch\Db\Sql\Update::processOption
     */
    public function testPrepareStatement()
    {
        // With empty option
        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('getPrepareType')->will($this->returnValue('positional'));
        $mockDriver->expects($this->any())->method('formatParameterName')->will($this->returnValue('?'));
        $mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, array($mockDriver, new TrustedSphinxQL()));
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $pContainer = new ParameterContainer(array());
        $mockStatement->expects($this->any())->method('getParameterContainer')->will($this->returnValue($pContainer));
        $mockStatement->expects($this->at(1))
        ->method('setSql')
        ->with($this->equalTo('UPDATE `foo` SET `bar` = ?, `boo` = NOW() WHERE x = y'));
        $this->update->table('foo')
        ->set(array('bar' => 'baz', 'boo' => new Expression('NOW()')))
        ->where('x = y');
        $this->update->prepareStatement($mockAdapter, $mockStatement);

        // Without parameter container
        $this->update = new Update;
        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('getPrepareType')->will($this->returnValue('positional'));
        $mockDriver->expects($this->any())->method('formatParameterName')->will($this->returnValue('?'));
        $mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, array($mockDriver, new TrustedSphinxQL()));
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('getParameterContainer')->will($this->returnValue(null));
        $mockStatement->expects($this->at(1))->method('setParameterContainer')->with($this->isInstanceOf('Zend\Db\Adapter\ParameterContainer'));
        $mockStatement->expects($this->at(2))
            ->method('setSql')
            ->with($this->equalTo('UPDATE `foo` SET `bar` = ?, `boo` = NOW() WHERE x = y OPTION `ranker` = ?, `max_matches` = ?, `field_weights` = (title=10, body=3)'));
        $this->update->table('foo')
            ->set(array('bar' => 'baz', 'boo' => new Expression('NOW()')))
            ->where('x = y')
            ->option(array('ranker' => 'bm25', 'max_matches' => 500, 'field_weights' => new Expression('(title=10, body=3)')));
        $this->update->prepareStatement($mockAdapter, $mockStatement);

        // With option
        $this->update = new Update;
        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('getPrepareType')->will($this->returnValue('positional'));
        $mockDriver->expects($this->any())->method('formatParameterName')->will($this->returnValue('?'));
        $mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, array($mockDriver, new TrustedSphinxQL()));
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $pContainer = new ParameterContainer(array());
        $mockStatement->expects($this->any())->method('getParameterContainer')->will($this->returnValue($pContainer));
        $mockStatement->expects($this->at(1))
            ->method('setSql')
            ->with($this->equalTo('UPDATE `foo` SET `bar` = ?, `boo` = NOW() WHERE x = y OPTION `ranker` = ?, `max_matches` = ?, `field_weights` = (title=10, body=3)'));
        $this->update->table('foo')
            ->set(array('bar' => 'baz', 'boo' => new Expression('NOW()')))
            ->where('x = y')
            ->option(array('ranker' => 'bm25', 'max_matches' => 500, 'field_weights' => new Expression('(title=10, body=3)')));
        $this->update->prepareStatement($mockAdapter, $mockStatement);

        // With TableIdentifier
        $this->update = new Update;
        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('getPrepareType')->will($this->returnValue('positional'));
        $mockDriver->expects($this->any())->method('formatParameterName')->will($this->returnValue('?'));
        $mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, array($mockDriver, new TrustedSphinxQL()));
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $pContainer = new ParameterContainer(array());
        $mockStatement->expects($this->any())->method('getParameterContainer')->will($this->returnValue($pContainer));
        $mockStatement->expects($this->at(1))
            ->method('setSql')
            ->with($this->equalTo('UPDATE `foo` SET `bar` = ?, `boo` = NOW() WHERE x = y OPTION `ranker` = ?, `max_matches` = ?, `field_weights` = (title=10, body=3)'));
        $this->update->table(new TableIdentifier('foo'))
            ->set(array('bar' => 'baz', 'boo' => new Expression('NOW()')))
            ->where('x = y')
            ->option(array('ranker' => 'bm25', 'max_matches' => 500, 'field_weights' => new Expression('(title=10, body=3)')));
        $this->update->prepareStatement($mockAdapter, $mockStatement);
    }

    /**
     * @covers SphinxSearch\Db\Sql\Update::getSqlString
     * @covers SphinxSearch\Db\Sql\Update::processOption
     */
    public function testGetSqlString()
    {
        // With empty option
        $this->update->table('foo')
        ->set(array('bar' => 'baz', 'boo' => new Expression('NOW()'), 'bam' => null))
        ->where('x = y');
        $this->assertEquals(
            'UPDATE `foo` SET `bar` = \'baz\', `boo` = NOW(), `bam` = NULL WHERE x = y',
            $this->update->getSqlString(new TrustedSphinxQL())
        );

        // With option
        $this->update = new Update;
        $this->update->table('foo')
            ->set(array('bar' => 'baz', 'boo' => new Expression('NOW()'), 'bam' => null))
            ->where('x = y')
            ->option(array('ranker' => 'bm25', 'max_matches' => 500, 'field_weights' => new Expression('(title=10, body=3)')));
        $this->assertEquals(
            'UPDATE `foo` SET `bar` = \'baz\', `boo` = NOW(), `bam` = NULL WHERE x = y OPTION `ranker` = \'bm25\', `max_matches` = 500, `field_weights` = (title=10, body=3)',
            $this->update->getSqlString(new TrustedSphinxQL())
        );

        // With TableIdentifier
        $this->update = new Update;
        $this->update->table(new TableIdentifier('foo'))
            ->set(array('bar' => 'baz', 'boo' => new Expression('NOW()'), 'bam' => null))
            ->where('x = y')
            ->option(array('ranker' => 'bm25', 'max_matches' => 500, 'field_weights' => new Expression('(title=10, body=3)')));
        $this->assertEquals(
            'UPDATE `foo` SET `bar` = \'baz\', `boo` = NOW(), `bam` = NULL WHERE x = y OPTION `ranker` = \'bm25\', `max_matches` = 500, `field_weights` = (title=10, body=3)',
            $this->update->getSqlString(new TrustedSphinxQL())
        );
    }

}

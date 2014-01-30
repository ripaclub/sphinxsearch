<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTests\Db\Sql;

use SphinxSearch\Db\Sql\Update;

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


}
 
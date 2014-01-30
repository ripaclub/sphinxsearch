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
            ->where('x = y');

        $this->assertEquals('foo', $this->update->getRawState('table'));
        $this->assertEquals(true, $this->update->getRawState('emptyWhereProtection'));
        $this->assertEquals(array('bar' => 'baz'), $this->update->getRawState('set'));
        // $this->assertEquals(, $this->update->getRawState('option')); // FIXME: option
        $this->assertInstanceOf('Zend\Db\Sql\Where', $this->update->getRawState('where'));
    }

}
 
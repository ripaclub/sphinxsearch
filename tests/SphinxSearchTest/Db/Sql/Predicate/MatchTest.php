<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Sql;


use SphinxSearch\Db\Sql\Predicate\Match;

class MatchTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Match
     */
    protected $match;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->match = new Match();
    }

    public function testSetExpression()
    {
        $this->assertInstanceOf('SphinxSearch\Db\Sql\Predicate\Match', $this->match->setExpression('TEST'));
        $this->assertEquals('MATCH(TEST)', $this->match->getExpression());

        $this->assertEquals(array(
            array('MATCH(TEST)', array(), array())
        ), $this->match->getExpressionData());

        $this->setExpectedException('SphinxSearch\Db\Sql\Exception\InvalidArgumentException');

        $this->match->setExpression(1); //not a string

    }


}

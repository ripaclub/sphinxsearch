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
use Zend\Db\Sql\Expression;

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
        $this->assertInstanceOf('SphinxSearch\Db\Sql\Predicate\Match', $this->match->setQuery('TEST'));
        $this->assertEquals('TEST', $this->match->getQuery());

        $this->assertEquals(array(
            array('MATCH(%1$s)', array('TEST'), array(Expression::TYPE_VALUE))
        ), $this->match->getExpressionData());

    }

    public function testSetGetSpecification()
    {
        $match = new Match();

        //test default specification
        $this->assertEquals('MATCH(%1$s)', $match->getSpecification());

        $this->assertInstanceOf('SphinxSearch\Db\Sql\Predicate\Match', $match->setSpecification('TEST_SPECIFICATION'));
        $this->assertEquals('TEST_SPECIFICATION', $match->getSpecification());

        $this->assertEquals(array(
            array('TEST_SPECIFICATION', array(''), array(Expression::TYPE_VALUE))
        ), $match->getExpressionData());

    }


}

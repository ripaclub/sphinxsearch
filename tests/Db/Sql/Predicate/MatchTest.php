<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Sql;

use SphinxSearch\Db\Sql\Predicate\Match;
use SphinxSearch\Query\QueryExpression;

/**
 * Class MatchTest
 */
class MatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Match
     */
    protected $match;

    /**
     * @testdox Constructor
     */
    public function test__constuctor()
    {
        // Assume that '?' is the placeholder
        $match = new Match('?', ['param']);

        $queryExpression = $match->getQuery();
        $this->assertInstanceOf('\SphinxSearch\Query\QueryExpression', $queryExpression);

        $this->assertEquals(['param'], $queryExpression->getParameters());
        $this->assertEquals('?', $queryExpression->getExpression());


        $queryExpression = new QueryExpression();
        $match = new Match($queryExpression);
        $this->assertSame($queryExpression, $match->getQuery());


        // Test invalid argument
        $this->setExpectedException('\SphinxSearch\Db\Sql\Exception\InvalidArgumentException');
        new Match(new \stdClass());

    }

    /**
     * @testdox Setter and getter query
     */
    public function testSetGetQuery()
    {
        $match = new Match();

        $queryExpression = new QueryExpression('test');

        $this->assertInstanceOf('\SphinxSearch\Db\Sql\Predicate\Match', $match->setQuery($queryExpression));
        $this->assertSame($queryExpression, $match->getQuery());

        $this->assertEquals(
            [
                ['MATCH(%1$s)', ['test'], [Match::TYPE_VALUE]]
            ],
            $match->getExpressionData()
        );
    }

    /**
     * @testdox Setter and getter specificaton
     */
    public function testSetGetSpecification()
    {
        $match = new Match();

        // Test default specification
        $this->assertEquals('MATCH(%1$s)', $match->getSpecification());

        $this->assertInstanceOf('\SphinxSearch\Db\Sql\Predicate\Match', $match->setSpecification('TEST_SPECIFICATION'));
        $this->assertEquals('TEST_SPECIFICATION', $match->getSpecification());

        $this->assertEquals(
            [
                ['TEST_SPECIFICATION', [''], [Match::TYPE_VALUE]]
            ],
            $match->getExpressionData()
        );
    }

    /**
     * @testdox Get expression data
     */
    public function testGetExpressionData()
    {
        $match = new Match('foo');
        $this->assertEquals(
            [[$match->getSpecification(), ['foo'], [Match::TYPE_VALUE]]],
            $match->getExpressionData()
        );
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->match = new Match();
    }
}

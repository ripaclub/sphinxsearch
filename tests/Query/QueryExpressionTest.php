<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Query;

use SphinxSearch\Query\QueryExpression;

/**
 * Class QueryExpressionTest
 */
class QueryExpressionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueryExpression
     */
    protected $queryExpression;

    /**
     * @testdox Setters and getters for expression
     */
    public function testSettersAndGettersExpression()
    {
        $this->assertInstanceOf('\SphinxSearch\Query\QueryExpression', $this->queryExpression->setExpression('?'));
        $this->assertSame($this->queryExpression->getExpression(), '?');

        $this->setExpectedException('\SphinxSearch\Query\Exception\InvalidArgumentException');
        $this->queryExpression->setExpression(['?']);
    }

    /**
     * @testdox Setters and getters for parameters
     */
    public function testSettersAndGettersParameters()
    {
        $this->assertInstanceOf(
            '\SphinxSearch\Query\QueryExpression',
            $this->queryExpression->setParameters(['ipsum'])
        );
        $this->assertSame($this->queryExpression->getParameters(), ['ipsum']);

        $this->setExpectedException('\SphinxSearch\Query\Exception\InvalidArgumentException');
        $this->queryExpression->setParameters(new \stdClass);
    }

    public function testEscapeString()
    {
        $this->assertEquals(
            '\(\|\-\)\@\!\~\&\"\/\\\\ abc def 123 ?',
            QueryExpression::escapeString('(|-)@!~&"/\\ abc def 123 ?')
        );
    }

    public function testToString()
    {
        $this->queryExpression->setExpression('?');
        $this->assertEmpty($this->queryExpression->toString());

        $this->queryExpression->setParameters('ipsum');
        $this->assertEquals('ipsum', $this->queryExpression->toString());

        $this->queryExpression->setParameters(['ipsum', 'dolor']);
        $this->setExpectedException('\SphinxSearch\Query\Exception\RuntimeException');
        $this->queryExpression->toString();
    }

    protected function setUp()
    {
        $this->queryExpression = new QueryExpression();
    }
}

<?php
/**
 * User: leodido
 * Date: 13/02/14
 * Time: 11.46
 */

namespace SphinxSearchTest\Query;

use SphinxSearch\Query\QueryExpression;

class QueryExpressionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var QueryExpression
     */
    protected $queryExpression;

    protected function setUp()
    {
        $this->queryExpression = new QueryExpression();
    }

    /**
     * @testdox Setters and getters for expression
     */
    public function testSettersAndGettersExpression()
    {
        $this->assertInstanceOf('SphinxSearch\Query\QueryExpression', $this->queryExpression->setExpression('?'));
        $this->assertSame($this->queryExpression->getExpression(), '?');

        $this->setExpectedException('SphinxSearch\Query\Exception\InvalidArgumentException');
        $this->queryExpression->setExpression(array('?'));
    }

    /**
     * @testdox Setters and getters for parameters
     */
    public function testSettersAndGettersParameters()
    {
        $this->assertInstanceOf('SphinxSearch\Query\QueryExpression',$this->queryExpression->setParameters(array('ipsum')));
        $this->assertSame($this->queryExpression->getParameters(), array('ipsum'));

        $this->setExpectedException('SphinxSearch\Query\Exception\InvalidArgumentException');
        $this->queryExpression->setParameters(new \stdClass);
    }

    public function testEscapeString()
    {
        $this->assertEquals(
            '\(\|\-\)\@\!\~\&\"\/\\\\ abc def 123 ?',
            QueryExpression::escapeString('(|-)@!~&"/\\ abc def 123 ?')
        );
    }

}
<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Sql;

use SphinxSearch\Db\Sql\ExpressionDecorator;
use Zend\Db\Sql\Expression;
use SphinxSearchTest\Db\TestAsset\TrustedSphinxQL;
use SphinxSearch\Db\Adapter\Platform\SphinxQL;

class ExpressionDecoratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ExpressionDecorator
     */
    protected $expr;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->expr = new ExpressionDecorator(new Expression, new SphinxQL());
    }

    public function test__construct()
    {
        $expr = new Expression();

        $decorator = new ExpressionDecorator($expr, new SphinxQL());

        $this->assertSame($expr, $decorator->getSubject());
    }

    public function testSetGetFloatAsLiteral()
    {
        //default option
        $this->assertTrue(ExpressionDecorator::getFloatAsLiteral());

        ExpressionDecorator::setFloatAsLiteral(false);
        $this->assertFalse(ExpressionDecorator::getFloatAsLiteral());

        ExpressionDecorator::setFloatAsLiteral(true);
        $this->assertTrue(ExpressionDecorator::getFloatAsLiteral());
    }

    public function testSetGetSubject()
    {
        $subject = new Expression();

        $this->assertInstanceOf('SphinxSearch\Db\Sql\ExpressionDecorator', $this->expr->setSubject($subject));
        $this->assertSame($subject, $this->expr->getSubject());
    }

    public function testGetExpressionData()
    {
        $subject = new Expression('?', array(33.0));
        $this->expr->setSubject($subject);

        ExpressionDecorator::setFloatAsLiteral(false);
        $this->assertSame(array(array('%s', array(33.0), array(Expression::TYPE_VALUE))), $this->expr->getExpressionData());

        $platform = new TrustedSphinxQL(); //use platform to ensure same float point precision
        ExpressionDecorator::setFloatAsLiteral(true);
        $this->assertSame(array(array('%s', array($platform->quoteTrustedValue(33.0)), array(Expression::TYPE_LITERAL))), $this->expr->getExpressionData());
    }

}
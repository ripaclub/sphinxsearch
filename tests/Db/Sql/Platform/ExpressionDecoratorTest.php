<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Sql\Platform;

use SphinxSearch\Db\Adapter\Platform\SphinxQL;
use SphinxSearch\Db\Sql\Platform\ExpressionDecorator;
use SphinxSearchTest\Db\TestAsset\TrustedSphinxQL;
use Zend\Db\Adapter\Platform\Mysql;
use Zend\Db\Sql\Expression;

/**
 * Class ExpressionDecoratorTest
 */
class ExpressionDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExpressionDecorator
     */
    protected $expr;

    /**
     * @var SphinxQL
     */
    protected $platform;

    public function test__construct()
    {
        $expr = new Expression();

        $decorator = new ExpressionDecorator($expr, new SphinxQL());

        $this->assertSame($expr, $decorator->getSubject());
    }

    public function test__constructShouldThrowExceptionWhenIsNotASphinxQLPlatform()
    {
        $this->setExpectedException(
            '\SphinxSearch\Db\Sql\Exception\InvalidArgumentException',
            '$platform must be an instance of \SphinxSearch\Db\Adapter\Platform\SphinxQL'
        );
        new ExpressionDecorator(new Expression(), new Mysql());
    }

    public function testSetGetSubject()
    {
        $subject = new Expression();

        $this->assertInstanceOf('\SphinxSearch\Db\Sql\Platform\ExpressionDecorator', $this->expr->setSubject($subject));
        $this->assertSame($subject, $this->expr->getSubject());
    }

    public function testGetExpressionData()
    {
        $subject = new Expression('1 <> 0');
        $this->expr->setSubject($subject);
        $this->assertSame(
            ['1 <> 0'],
            $this->expr->getExpressionData()
        );


        $subject = new Expression('? = ?', [33.0, true]);
        $this->expr->setSubject($subject);

        $this->platform->enableFloatConversion(false);
        $this->assertSame(
            [['%s = %s', [33.0, 1], [Expression::TYPE_VALUE, Expression::TYPE_VALUE]]],
            $this->expr->getExpressionData()
        );

        $platform = new TrustedSphinxQL(); // Use platform to ensure same float point precision
        $platform->enableFloatConversion(true);
        $this->platform->enableFloatConversion(true);
        $this->assertSame(
            [
                [
                    '%s = %s',
                    [$platform->quoteTrustedValue(33.0), 1],
                    [Expression::TYPE_LITERAL, Expression::TYPE_VALUE]
                ]
            ],
            $this->expr->getExpressionData()
        );
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->platform = new SphinxQL();
        $this->expr = new ExpressionDecorator(new Expression, $this->platform);
    }
}

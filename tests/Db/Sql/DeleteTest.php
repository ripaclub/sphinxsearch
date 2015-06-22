<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Sql;

use SphinxSearch\Db\Adapter\Platform\SphinxQL;
use SphinxSearch\Db\Sql\Delete;
use SphinxSearch\Db\Sql\Platform\ExpressionDecorator;
use SphinxSearchTest\Db\TestAsset\TrustedSphinxQL;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\TableIdentifier;
use Zend\Version\Version;

/**
 * Class DeleteTest
 */
class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Delete
     */
    protected $delete;

    /**
     * @covers SphinxSearch\Db\Sql\Delete::from
     */
    public function testTable()
    {
        $this->delete->from('foo', 'bar');
        $this->assertEquals('foo', $this->readAttribute($this->delete, 'table'));

        $tableIdentifier = new TableIdentifier('foo', 'bar');
        $this->delete->from($tableIdentifier);
        $this->assertEquals('foo', $this->readAttribute($this->delete, 'table'));
    }

    /**
     * @testdox Method processExpression() methods will return proper array when internally called, part of extension API
     * @covers SphinxSearch\Db\Sql\Delete::processExpression
     */
    public function testProcessExpression()
    {
        $delete = new Delete();
        $mockDriver = $this->getMock('\Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('formatParameterName')->will($this->returnValue('?'));
        $parameterContainer = new ParameterContainer();

        $selectReflect = new \ReflectionObject($delete);
        $mr = $selectReflect->getMethod('processExpression');
        $mr->setAccessible(true);

        //Test with an Expression
        $return = $mr->invokeArgs(
            $delete,
            [new Expression('?', 10.1), new TrustedSphinxQL(), $mockDriver, $parameterContainer]
        );

        $this->assertInternalType('string', $return);

        //Test with an ExpressionDecorator
        $return2 = $mr->invokeArgs(
            $delete,
            [
                new ExpressionDecorator(new Expression('?', 10.1), new SphinxQL()),
                new TrustedSphinxQL(),
                $mockDriver,
                $parameterContainer
            ]
        );

        $this->assertInternalType('string', $return2);

        $this->assertSame($return, $return2);
        $this->assertEquals('10.1', $return);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->delete = new Delete();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
}

<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Sql;

use SphinxSearch\Db\Sql\Show;
use SphinxSearchTest\Db\TestAsset\TrustedSphinxQL;
use Zend\Db\Adapter\ParameterContainer;

/**
 * Class ShowTest
 */
class ShowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Show
     */
    protected $show;

    /**
     * @testdox Method getRawState() returns default values
     * @covers SphinxSearch\Db\Sql\Show::getRawState
     */
    public function testDefaultsViaGetRawState()
    {
        $this->assertEquals(Show::SHOW_META, $this->show->getRawState(Show::SHOW));
        $this->assertEmpty($this->show->getRawState(Show::LIKE));
        $this->assertEquals([Show::SHOW => Show::SHOW_META, Show::LIKE => null], $this->show->getRawState());
    }

    /**
     * @testdox Method show()
     * @covers  SphinxSearch\Db\Sql\Show::show
     * @depends testDefaultsViaGetRawState
     */
    public function testShow()
    {
        $this->assertInstanceOf('\SphinxSearch\Db\Sql\Show', $this->show->show(Show::SHOW_WARNINGS));
        $this->assertEquals(Show::SHOW_WARNINGS, $this->show->getRawState(Show::SHOW));

        $this->setExpectedException('\SphinxSearch\Db\Sql\Exception\InvalidArgumentException');
        $this->show->show('invalid value');
    }

    /**
     * @testdox Method like()
     * @covers  SphinxSearch\Db\Sql\Show::like
     * @depends testDefaultsViaGetRawState
     */
    public function testLike()
    {
        $this->assertInstanceOf('\SphinxSearch\Db\Sql\Show', $this->show->like('foo'));
        $this->assertEquals('foo', $this->show->getRawState(Show::LIKE));
    }

    /**
     * @testdox Method prepareStatement() will produce expected sql and parameters
     * @covers  SphinxSearch\Db\Sql\Show::prepareStatement
     * @covers  SphinxSearch\Db\Sql\Show::processLike
     * @depends testShow
     * @depends testLike
     */
    public function testPrepareStatement()
    {
        $this->show->show(Show::SHOW_META)
            ->like('bar');

        $expectedSqlString = 'SHOW META LIKE ?';
        $expectedParameters = ['like' => 'bar'];

        $useNamedParameters = false;
        $mockDriver = $this->getMock('\Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('formatParameterName')->will(
            $this->returnCallback(
                function ($name) use ($useNamedParameters) {
                    return (($useNamedParameters) ? ':' . $name : '?');
                }
            )
        );
        $mockAdapter = $this->getMock('\Zend\Db\Adapter\Adapter', null, [$mockDriver, new TrustedSphinxQL()]);

        $parameterContainer = new ParameterContainer();

        $mockStatement = $this->getMock('\Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('getParameterContainer')->will(
            $this->returnValue($parameterContainer)
        );
        $mockStatement->expects($this->any())->method('setSql')->with($this->equalTo($expectedSqlString));

        $this->show->prepareStatement($mockAdapter, $mockStatement);
        $this->assertEquals($expectedParameters, $parameterContainer->getNamedArray());

        //test without ParameterContainer
        $mockStatement = $this->getMock('\Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('getParameterContainer')->will($this->returnValue(null));
        $mockStatement->expects($this->any())->method('setParameterContainer')->with(
            $this->isInstanceOf('\Zend\Db\Adapter\ParameterContainer')
        );
        $mockStatement->expects($this->any())->method('setSql')->with($this->equalTo($expectedSqlString));
        $this->show->prepareStatement($mockAdapter, $mockStatement);

    }

    /**
     * @testdox Method getSqlString() will produce expected sql and parameters based on a variety of provided arguments [uses data provider]
     * @covers  SphinxSearch\Db\Sql\Show::getSqlString
     * @covers  SphinxSearch\Db\Sql\Show::processLike
     * @depends testShow
     * @depends testLike
     */
    public function testGetSqlString()
    {
        $this->show->show(Show::SHOW_META)
            ->like('bar');

        $expectedSqlString = 'SHOW META LIKE \'bar\'';
        $this->assertEquals($expectedSqlString, $this->show->getSqlString(new TrustedSphinxQL()));

        $this->show->show(Show::SHOW_WARNINGS)
            ->like(null);

        $expectedSqlString = 'SHOW WARNINGS';
        $this->assertEquals($expectedSqlString, $this->show->getSqlString(new TrustedSphinxQL()));
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->show = new Show();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
}

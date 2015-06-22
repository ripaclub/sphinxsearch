<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Sql;

use SphinxSearch\Db\Sql\Replace;
use SphinxSearchTest\Db\TestAsset\TrustedSphinxQL;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\TableIdentifier;

/**
 * Class ReplaceTest
 */
class ReplaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Replace
     */
    protected $replace;

    /**
     * @covers SphinxSearch\Db\Sql\Replace::prepareStatement
     */
    public function testPrepareStatement()
    {
        $mockDriver = $this->getMock('\Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('getPrepareType')->will($this->returnValue('positional'));
        $mockDriver->expects($this->any())->method('formatParameterName')->will($this->returnValue('?'));
        $mockAdapter = $this->getMock('\Zend\Db\Adapter\Adapter', null, [$mockDriver, new TrustedSphinxQL()]);

        $mockStatement = $this->getMock('\Zend\Db\Adapter\Driver\StatementInterface');
        $pContainer = new ParameterContainer([]);
        $mockStatement->expects($this->any())->method('getParameterContainer')->will($this->returnValue($pContainer));
        $mockStatement->expects($this->at(1))
            ->method('setSql')
            ->with($this->equalTo('REPLACE INTO `foo` (`bar`, `boo`) VALUES (?, NOW())'));

        $this->replace->into('foo')
            ->values(['bar' => 'baz', 'boo' => new Expression('NOW()')]);

        $this->replace->prepareStatement($mockAdapter, $mockStatement);

        // with TableIdentifier
        $this->replace = new Replace;
        $mockDriver = $this->getMock('\Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('getPrepareType')->will($this->returnValue('positional'));
        $mockDriver->expects($this->any())->method('formatParameterName')->will($this->returnValue('?'));
        $mockAdapter = $this->getMock('\Zend\Db\Adapter\Adapter', null, [$mockDriver, new TrustedSphinxQL()]);

        $mockStatement = $this->getMock('\Zend\Db\Adapter\Driver\StatementInterface');
        $pContainer = new ParameterContainer([]);
        $mockStatement->expects($this->any())->method('getParameterContainer')->will($this->returnValue($pContainer));
        $mockStatement->expects($this->at(1))
            ->method('setSql')
            ->with($this->equalTo('REPLACE INTO `foo` (`bar`, `boo`) VALUES (?, NOW())'));

        $this->replace->into(new TableIdentifier('foo'))// FIXME: SphinxQL does not support schema
        ->values(['bar' => 'baz', 'boo' => new Expression('NOW()')]);

        $this->replace->prepareStatement($mockAdapter, $mockStatement);
    }

    /**
     * @covers SphinxSearch\Db\Sql\Replace::getSqlString
     */
    public function testGetSqlString()
    {
        $this->replace->into('foo')
            ->values(['bar' => 'baz', 'boo' => new Expression('NOW()'), 'bam' => null]);

        $this->assertEquals(
            'REPLACE INTO `foo` (`bar`, `boo`, `bam`) VALUES (\'baz\', NOW(), NULL)',
            $this->replace->getSqlString(new TrustedSphinxQL())
        );

        // with TableIdentifier
        $this->replace = new Replace;
        $this->replace->into(new TableIdentifier('foo'))// FIXME: SphinxQL does not support schema
        ->values(['bar' => 'baz', 'boo' => new Expression('NOW()'), 'bam' => null]);

        $this->assertEquals(
            'REPLACE INTO `foo` (`bar`, `boo`, `bam`) VALUES (\'baz\', NOW(), NULL)',
            $this->replace->getSqlString(new TrustedSphinxQL())
        );
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->replace = new Replace;
    }
}

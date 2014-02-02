<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Adapter\Driver\Pdo;

use SphinxSearch\Db\Adapter\Driver\Pdo\Statement;
use Zend\Db\Adapter\ParameterContainer;

class StatementTest  extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Statement
     */
    protected $statement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pdoStatementMock = null;

    /**
     * Sets up.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->statement = new Statement;
        $this->statement->setDriver(
            $this->getMock('Zend\Db\Adapter\Driver\Pdo\Pdo', array('createResult'), array(), '', false)
        );
        $this->statement->initialize(new TestAsset\CtorlessPdo(
                $this->pdoStatementMock = $this->getMock('PDOStatement', array('execute', 'bindParam')))
        );
    }

    /**
     * Tears down.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @testdox Statemement execution will convert PHP boolean to Pdo boolean when binding
     */
    public function testBool()
    {
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo('foo'),
            $this->equalTo(false),
            $this->equalTo(null)
        );
        $this->statement->execute(array('foo' => false));
        //
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo('foo'),
            $this->equalTo(false),
            $this->equalTo(\PDO::PARAM_BOOL)
        );
        $paramContainer = new ParameterContainer();
        $paramContainer->offsetSet('foo', false, 'bool');
        $this->statement->setParameterContainer($paramContainer);
        $this->statement->execute(array('foo' => false));
    }

}
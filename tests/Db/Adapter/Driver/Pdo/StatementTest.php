<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Adapter\Driver\Pdo;

use SphinxSearch\Db\Adapter\Driver\Pdo\Statement;
use SphinxSearchTest\Db\Adapter\Driver\Pdo\TestAsset\ParametersBoundedAlreadyStatement;
use Zend\Db\Adapter\ParameterContainer;

/**
 * Class StatementTest
 */
class StatementTest extends \PHPUnit_Framework_TestCase
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
     * @testdox Statemement execution will always convert PHP null to Pdo null (i.e., internal auto casting) when binding
     */
    public function testNull()
    {
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo('foo'),
            $this->equalTo(null),
            $this->equalTo(\PDO::PARAM_NULL)
        );
        $this->statement->execute(['foo' => null]);
        $paramContainer = new ParameterContainer();
        $paramContainer->offsetSet('foo', null, ParameterContainer::TYPE_NULL);
        $this->statement->setParameterContainer($paramContainer);
        $this->statement->execute(['foo' => null]);
    }

    /**
     * @testdox Statemement execution will convert PHP boolean to Pdo null (i.e., internal auto casting) when binding
     */
    public function testBool()
    {
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo('foo'),
            $this->equalTo(false),
            $this->equalTo(null)
        );
        $this->statement->execute(['foo' => false]);
    }

    /**
     * @testdox Statement execution will convert PHP bool to Pdo null (i.e., internal auto casting) when binding through parameter container
     */
    public function testBoolWithParamContainer()
    {
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo('foo'),
            $this->equalTo(false),
            $this->equalTo(null)
        );
        $paramContainer = new ParameterContainer();
        $paramContainer->offsetSet('foo', false, 'bool');
        $this->statement->setParameterContainer($paramContainer);
        $this->statement->execute(['foo' => false]);
    }

    /**
     * @testdox Statemement execution will convert PHP int to Pdo null (i.e., internal auto casting) when binding
     */
    public function testInteger()
    {
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo('foo'),
            $this->equalTo(3),
            $this->equalTo(null)
        );
        $this->statement->execute(['foo' => 3]);
    }

    /**
     * @testdox Statement execution will convert PHP int to Pdo int when binding through parameter container
     */
    public function testIntegerWithParamContainer()
    {
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo('foo'),
            $this->equalTo(3),
            $this->equalTo(\PDO::PARAM_INT) //Forcing PDO type
        );
        $paramContainer = new ParameterContainer();
        $paramContainer->offsetSet('foo', 3, ParameterContainer::TYPE_INTEGER);
        $this->statement->setParameterContainer($paramContainer);
        $this->statement->execute(['foo' => 3]);
    }

    /**
     * @testdox Pdo automatic internal casting of statement double parameters
     */
    public function testDouble()
    {
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo('foo'),
            $this->equalTo(3.333),
            $this->equalTo(null) // Auto PDO type detection
        );
        $this->statement->execute(['foo' => 3.333]);
    }

    /**
     * @testdox Double value forced to float PHP side
     */
    public function testDoubleWithParamContainer()
    {
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo('foo'),
            $this->equalTo(3.333),
            $this->equalTo(null) // Auto PDO type detection
        );
        $paramContainer = new ParameterContainer();
        $paramContainer->offsetSet('foo', 3.333, ParameterContainer::TYPE_DOUBLE);
        $this->statement->setParameterContainer($paramContainer);
        $this->statement->execute(['foo' => 3.333]);
    }

    /**
     * @requires OS LINUX|DARWIN
     * @testdox LOB value
     */
    public function testLOB()
    {
        $fp = fopen('/dev/random', 'rb');
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo('lob'),
            $this->equalTo($fp),
            $this->equalTo(null) // Auto PDO type detection
        );
        $this->statement->execute(['lob' => $fp]);
    }

    /**
     * @requires OS LINUX|DARWIN
     * @testdox LOB value passed through a parameter container
     */
    public function testLOBWithParamContainer()
    {
        $fp = fopen('/dev/random', 'rb');
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo('lob'),
            $this->equalTo($fp),
            $this->equalTo(\PDO::PARAM_LOB) // Auto PDO type detection
        );
        $paramContainer = new ParameterContainer();
        $paramContainer->offsetSet('lob', $fp, ParameterContainer::TYPE_LOB);
        $this->statement->setParameterContainer($paramContainer);
        $this->statement->execute(['lob' => $fp]);
    }

    /**
     * This test is required to assure future compatibility with parent statement class provided by zend.
     *
     * We're testing the not used property $this->parametersBound simulating it was true.
     *
     */
    public function testParametersBoundedAlready()
    {
        $statement = new ParametersBoundedAlreadyStatement();
        $statement->setDriver(
            $this->getMock('\Zend\Db\Adapter\Driver\Pdo\Pdo', [], [], '', false)
        );
        $statement->initialize(
            new TestAsset\CtorlessPdo(
                $pdoStatementMock = $this->getMock('\PDOStatement', ['bindParam'])
            )
        );

        //bind param should be never called becaouse we set parametersBound = true in ParametersBounededAlteradStatement test asset
        $pdoStatementMock->expects($this->never())->method('bindParam');
        $statement->execute(['dummy' => 'dummy']);
    }

    /**
     * Sets up.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->statement = new Statement;
        $this->statement->setDriver(
            $this->getMock('\Zend\Db\Adapter\Driver\Pdo\Pdo', ['createResult'], [], '', false)
        );
        $this->statement->initialize(
            new TestAsset\CtorlessPdo(
                $this->pdoStatementMock = $this->getMock('\PDOStatement', ['execute', 'bindParam'])
            )
        );
    }

    /**
     * Tears down.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
}
